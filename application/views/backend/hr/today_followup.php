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
                <th>Actions</th>
            </tr>
          </thead>
		  
          <tbody>
              
            <?php foreach($orders as $key => $item): ?>
            <tr>
              <td><?php echo $key+1;?></td> 
              <td><?php echo $item['name'];?> <br> <?php echo $item['phone'];?></td>
              <td><?php echo $item['type'];?></td>
              <td><?php echo $item['follow_up_date'];?></td>
              <td><?php echo $item['follow_up_time'];?></td>
              
                  
                  <td><button  type="button" class="btn icon-btn mr-1 mb-1"
                type="button"
                data-bs-toggle="offcanvas"
                data-bs-target="#offcanvasEnd"
                aria-controls="offcanvasEnd"
                onclick="get_timeline_(<?php echo $item['doctor_id'];?>);"><span>View Timeline</span></button></td>
              
            </tr>
            <?php endforeach; ?>
          </tbody>   
		   <?php if (empty($orders)): ?> 
			<tr>
			   <td colspan="10"><p class="notf">Followup Not Found</p></td>
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
		<div class="offcanvas-footer">
			<a  href="#" id="followup_btn" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Followup"><button type="button" class="btn btn-primary mb-1 d-grid w-100">Add Remark</button> </a>
		</div>   
	</div>



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
              let furl="<?= base_url().'modal/popup/model_candidate_followup/';?>"+b;
              $("#followup_btn").attr("onclick", "smallAjaxModal('"+furl+"', 'Add Followup')");
          }
    })
} 
</script>