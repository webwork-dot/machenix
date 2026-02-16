
<!-- Bordered table start -->
<div class="row" id="table-bordered">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="row">
         <div class="col-sm-12 col-md-4 align-ver">
             <label class="mb-0 bsumit">Total: <?php echo $total_count ?></label>
         </div>
         <div class="col-sm-12 col-md-5 mb-0">
             <form class="form form-vertical" method="GET">
              <div class="form-body">
                 <div class="row"> 
                      <div class="col-md-7">
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
        <div class="col-sm-12 col-md-3">
                  <a style="float:right;" href="<?php echo site_url('admin/city/add'); ?>" class="dt-button add-new btn btn-primary" tabindex="0" aria-controls="DataTables_Table_0" ><span><i data-feather='plus'></i> Add City</span></a>
         </div>
        </div>
      </div>
      <div class="card-body1">
        <table class="table table-bordered">
          <thead>
            <tr>
                <th>#</th>
                <th>State</th>
                <th>City</th>
                <th>Actions</th>
            </tr>
          </thead>
		 
          <tbody>
            <?php foreach($orders as $key => $item): ?>
            <tr>
              <td><?php echo $key+1;?></td> 
              <td><?php echo $item['state'];?></td>
              <td><?php echo $item['name'];?></td>
              <td>
                  <a href="<?php echo site_url('admin/city/edit/'.$item['id']); ?>" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i data-feather="edit-2"></i></button> </a> 
                </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
		 
          </table>
          <?php if (empty($orders)): ?>
               <p class="notf" class="">City Data Not Found</p>
           <?php endif; ?>
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