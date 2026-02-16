
<div class="row" id="table-bordered">
  <div class="col-12">
    <div class="card">
	 <?php include '_filter_process.php';?>
	  
	  
      <div class="card-body1">
        <table class="table table-bordered leads-table tfixed">
          <thead>
            <tr>
                <th>#</th>
                <th>Candidate Name</th>
                <th>Type</th>
                <th>State</th>
                <th>City</th>
                <th>HR Name</th>
                <th style="width:180px">Action</th>
            </tr>
          </thead>
          
          <tbody>
            <?php $start= page_number(20);
				foreach($orders as $key => $item): ?>
			<tr>
			  <td><?php echo ++$start;?></td>
			  <td><a href="<?php echo site_url('hr/candidate/edit/'.$item['id']); ?>" style="color: #206931;cursor: pointer;text-decoration: underline;"><?php echo $item['name'];?> <br> <?php echo $item['phone'];?></a></td>  
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
	            <a href="javascript::" onclick="showAjaxModal('<?php echo site_url('modal/popup/modal_schedule_interview/'.$item['id']); ?>', 'Schedule Interview')" data-bs-toggle="tooltip" data-bs-placement="bottom" ><button type="button" class="btn mr-1 mb-1 icon-btn-edit">Schedule Interview</button></a>
	            <a href="javascript::" onclick="showAjaxModal('<?php echo site_url('modal/popup/modal_reject/'.$item['id']); ?>', 'Reject')" data-bs-toggle="tooltip" data-bs-placement="bottom" ><button type="button" class="btn mr-1 mb-1 icon-btn-del">Reject</button></a>
	            </td>
			  
          	</tr>
			<?php endforeach; ?>
			
          </tbody>
		  <?php if (empty($orders)): ?> 
			<tr>
			   <td colspan="7"><p class="notf">Data Not Found</p></td>
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