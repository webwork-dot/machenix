     <div class="card-body">
            <div class="row">
               <div class="col-sm-12 col-md-4 mb-0 align-ver">
                   <label class="mb-0 bsumit">Total <?= $page_title?> : <?php echo $total_count ?></label>
               </div> 
			   
			   
			 <div class="col-sm-12 col-md-8 mb-0">
    		  <form class="form form-vertical" method="GET">
    			 <div class="form-body">
    				<div class="row">
    				   <div class="col-md-12 d-filter">					   
							<div class="form-group mb-0 mr-2">
							<select class="form-select" name="staff_type" placeholder="Staff Type">
								<option value="" >Select Staff Type</option>
								 <?php 
								 $staff_types=$this->hr_model->get_filter_staff_type();
								 foreach($staff_types as $stype){?>
								 <option value="<?php echo $stype['id'];?>" <?php if($this->input->get('staff_type') == $stype['id']){ echo 'selected';}?>><?php echo $stype['name'];?></option>
								 <?php }?>
							 </select>
						   </div>
					   
    					  <div class="form-group mb-0 mr-2">
    						 <input name="keywords" class="form-control" placeholder="Keywords" type="keywords" value="<?php echo html_escape($this->input->get('keywords', true)); ?>">
    					  </div>
    			  
    					  <div class="form-group mb-0">
    						 <button type="submit" name="search" value="true" class="btn btn-primary mr-1 mb-0">Search</button>
    						 <?php if(isset($_GET['search'])):?>
    						 <a href="<?php echo currentUrl($_SERVER["REQUEST_URI"]);?>"><button type="button" class="btn btn-outline-danger mr-1 mb-0">Reset</button> </a>
    						 <?php endif;?>
    					  </div>
    				   </div>
    				</div>
    			 </div>
    		  </form>
    	   </div>
			   
             
            </div>
         </div>
         