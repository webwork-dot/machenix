<!-- Bordered table start -->
<div class="row" id="table-bordered">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="row">
         <div class="col-sm-12 col-md-5">
             <label class="mb-0 bsumit">Total: <?php echo $total_count;?></label>
         </div>
         <div class="col-sm-12 col-md-2"></div>
         <div class="col-sm-12 col-md-5 mb-0">
             <form class="form form-vertical" method="GET">
              <div class="form-body">
                 <div class="row"> 
                      <div class="col-md-2"></div>
                      <div class="col-md-5">
                         <div class="form-group">
                            <input name="keywords" class="form-control" placeholder="Keywords" type="keywords" value="<?php echo html_escape($this->input->get('keywords', true)); ?>">
                         </div>
                      </div>
                      <div class="col-md-5 col-12 no-padd">
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
        </div>
      </div>
      
      <div class="card-body1">
        <table class="table table-bordered">
          <thead>
            <tr>
                <th>#</th>
                <th>Candidate Name</th>
                <th>Called Type</th>
                <th>Follow Date</th>
                <th>Follow Time</th>
                <th>Last Call Date & Time</th>
            </tr>
          </thead>
		  
          <tbody>
             
            <?php $start= page_number(20);
			foreach($orders as $key => $item): ?>
            <tr>
			  <td><?php echo ++$start;?></td>
              <td><?php echo $item['name'];?> <br> <?php echo $item['phone'];?></td>
              <td><?php echo $item['type'];?></td>
              <td><?php echo $item['follow_up_date'];?></td>
              <td><?php echo $item['follow_up_time'];?></td>
              <td><?php echo $item['added_date'];?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>    
		   <?php if (empty($orders)): ?> 
			<tr>
			   <td colspan="10"><p class="notf">Other Followup Not Found</p></td>
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
<!-- Bordered table end --