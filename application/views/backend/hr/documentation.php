
<div class="row" id="table-bordered">
   <div class="col-12">
      <div class="card">
	  
	      <div class="top-header">
             <div class="row ">   
                <div class="col-md-6"> 
                   <a class="nav-link active" href="<?php echo base_url();?>hr/documentation" >Pending <b>(<?php echo $pending_count;?>)</b></a>
                </div>
                <div class="col-md-6"> 
                   <a class="nav-link " href="<?php echo base_url(); ?>hr/verified-documentation">Not Verified <b>(<?php echo $verified_count;?>)</b></a>
                </div>             
             </div>
         </div>
	  
	 <?php include '_filter_process.php';?>
	  
         
         

         <div class="card-body1">
            <table class="table table-bordered">
               <thead>
                  <tr>
                    <th>#</th>
					<th>Candidate Name</th>
					<th>Type</th>
					<th>State</th>
					<th>City</th>
					<th>HR Name</th>
					<th>Action</th>
                  </tr>
               </thead>
              
                  <tbody>
                    <?php 
					$start= page_number(20);
					foreach($orders as $key => $item): ?>
					
                    <tr class="row-<?= $item['id'];?>">
                      <td><?php echo ++$start;?></td>
					  <td><a href="<?php echo site_url('hr/candidate/edit/'.$item['id']); ?>" style="color: #206931;cursor: pointer;text-decoration: underline;"><?php echo $item['name'];?> <br> <?php echo $item['phone'];?></a></td>  
					  <td><?php echo $item['staff_type'];?></td>
					  <td><?php echo $item['state_name'];?></td>
					  <td><?php echo $item['city_name'];?></td>
					  <td><?php echo $item['added_by_name'];?></td>
					  <td>
					  	
						  <a href="<?php echo $item['link'];?>" target="_blank" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Documentation Link"><button type="button" class="btn mr-1 mb-1 icon-btn-pass"><i class="feather icon-link"></i> Manually Verify</button> </a>
                
					  
					     <a href="#" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Resend Link SMS"  onclick="send_sms_link('<?= $item['id'];?>')"><button type="button" class="btn mr-1 mb-1 icon-btn-edit"><i class="feather icon-link"></i></button> </a>						
				
						
						  <a href="<?= hr_url().'candidate-details/'. $item['id'];?>" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Details"><button type="button" class="btn mr-1 mb-1 icon-btn"><i class="feather icon-eye"></i></button> </a>
                
						
						<button  type="button" class="btn icon-btn mr-1 mb-1"
						type="button"
						data-bs-toggle="offcanvas"
						data-bs-target="#offcanvasEnd"
						aria-controls="offcanvasEnd"
						onclick="get_timeline_(<?php echo $item['id'];?>);"><span>View Timeline</span></button> <br/>
						</td>
					</tr>                   
                    <?php endforeach; ?>
                  </tbody>  

              <?php if (empty($orders)): ?> 
                <tr>
                   <td colspan="10"><p class="notf">Documentation Not Found</p></td>
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

   function send_sms_link(id) {  
     
      var href =  "<?php echo base_url();?>hr/send_sms_link";
      var confirmDlg = duDialog(null, "Are you sure you want to Resend SMS Link?", {
        init: true,
        dark: false, 
        buttons: duDialog.OK_CANCEL,
        okText: 'Proceed',
        callbacks: {
          okClick: function(e) {
            $(".dlg-actions").find("button").attr("disabled",true);
            $(".ok-action").html('<i class="fa fa-spinner fa-pulse"></i> Please wait!');
            $(".loader").show();  
            $.ajax({
              type: 'POST',
              url: href,
              dataType: 'json', 
              data: {id:id},
            })
            .done(function(res) {
              confirmDlg.hide();
              if (res.status == '200') {
                $(".loader").hide(); 
                 Swal.fire({
            		title: "Success!",
            		text: res.message,
            		icon: "success",
            		customClass: {
            			confirmButton: "btn btn-primary"
            		},
            		buttonsStyling: !1
            	  }).then(() => {window.location.href = res.url;}); 
        		
              } else {
                  $(".loader").hide(); 
                    Swal.fire({
            			title: "Error!",
            			text: res.message ,
            			icon: "error",
            			customClass: {
            				confirmButton: "btn btn-primary"
            			},
            			buttonsStyling: !1
            		})  
              }
            })
            .fail(function(response) {
                $(".loader").fadeOut("slow");  
                Swal.fire({
        			title: "Error!",
        			text: res.message ,
        			icon: "error",
        			customClass: {
        				confirmButton: "btn btn-primary"
        			},
        			buttonsStyling: !1
        		})
            });
          }
        }
      });
      confirmDlg.show();
   } 
</script>