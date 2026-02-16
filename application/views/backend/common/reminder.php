<link rel="stylesheet" type="text/css" href="<?= base_url();?>app-assets/vendors/css/tables/datatable/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>

<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/datatables.buttons.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/jszip.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/pdfmake.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/vfs_fonts.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/buttons.html5.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/buttons.print.min.js"></script>
 


<div class="filter-accordion accordion mx-filter" id="accordionFilter">
   <div class="collapse-margin card ">
      <div class="card-header" id="headingOne" data-toggle="collapse" role="button" data-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
         <span class="lead collapse-title">
            <h4 class="mb-0"><i class="feather icon-filter"></i> Filter </h4>
         </span>
      </div>
      <div id="collapseOne" class="collapse show pb-1" aria-labelledby="headingOne" data-parent="#accordionFilter">
         <section class="filter-section">
            <div class="row match-height">
               <div class="col-12">
                  <div class="card mb-0">
                     <div class="card-content">
                        <div class="card-body">
                           <form class="form form-vertical" id="form_filter" method="GET" onsubmit ='return checkForm(this);'>
                              <div class="form-body">
                                 <div class="row">
                                    <input type="hidden" name="search" value="true">
                                     <div class="col-md-3">
										 <div class="form-group mb-0">   
										  <label class="form-label">Reminder Date</label>
										  <div class="form-group">
											<input type="text" class="form-control flatpickr-range" name="date_range" value="<?php if(isset($_GET['date_range'])) { echo $_GET['date_range']; }?>" placeholder="Search  Date" autocomplete="off" readonly>
										 </div>
										</div>
									 </div>   
									
                                    <div class="col-md-3 col-12">
                                       <div class="form-group mb-0">
                                          <label  class="form-label">keywords</label>
                                          <div class="form-group">
                                             <input name="keywords" class="form-control" placeholder="Keywords" type="keywords" value="<?php if(isset($_GET['keywords'])) { echo $_GET['keywords']; }?>">
                                          </div>
                                       </div>
                                    </div>
									
                                    <div class="col-md-3">
                                       <label style="display: block;">&nbsp; </label>
                                       <div class="form-group mb-0">
                                          <button type="submit" name="search" value="true" id="btn_verify"  class="btn btn-primary btn_verify mr-1 mb-0">Search</button>
                                          <?php if(isset($_GET['search'])):?>
                                          <a href="<?php echo currentUrl($_SERVER["REQUEST_URI"]);?>"><button type="button" class="btn btn-outline-danger mr-1 mb-0 " id="show">Reset</button> </a>
                                          <?php endif;?>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </form>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </section>
      </div>
   </div>
</div>




<div class="row"> 
  <div class="col-12 mx-tabs">
   <div class="card-body nev-card"> 
      <ul class="nav nav-tabs nav-tabs-solid mb-0" id="scroll-1">  
         <li class="nav-item"><a class="nav-link <?php if ($status == 'pending')echo 'active';?>" href="<?= base_url().'common/reminder';?>">Pending Reminder</a></li>
	  
		 <li class="nav-item"><a class="nav-link <?php if ($status == 'done')echo 'active';?>" href="<?= base_url().'common/reminder-done';?>">Done Reminder</a></li>
	 </ul>
   </div>
  </div> 
</div>	
	
<!-- Bordered table start -->
<div class="row" id="table-bordered">
  <div class="col-12">
    <div class="card">
        
       <div class="card-body">
          <div class="row"> 
          <div class="col-md-12 mt-10">
              <h5 class="mb-0"><b>Total Reminder Data <span id="total_count"> (0)</span></b></h5>
          </div>
         </div> 
        </div>
        
        <div class="card-datatable d-report mb-2">
           <a href="<?php echo base_url('common/reminder/add'); ?>" class="dt-button add-new add-btn btn btn-primary" tabindex="0" aria-controls="DataTables_Table_0" ><span> <i class="feather icon-plus"></i> Add Reminder</span></a>
          <table class="table mx-table" id="report-datatable">
          <thead>
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Description</th>
                <th>Reminder Date</th>
                <th>Added Date</th>
				<?php if ($status == 'done'){?>
                <th>Done Date</th>
				<?php } ?>
                <th>Actions</th>
             </tr>
          </thead>
         </table>
      </div>
    </div>
  </div>
</div>





<script type="text/javascript">
  
  $(document).ready(function($) { 
        var dataTable = $('#report-datatable').DataTable({ 
            "dom": '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l B><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            "ordering": false,
            "searching": false,
            "sDom": 'rt<"dtPagination"lp><"clear">',
            "pagingType": "simple_numbers",
            "processing": true,
            'scrollX': true,
            "serverSide": true, 
            "lengthChange": true,  
			"lengthMenu": [10,25, 50, 100, 250],
            "language" : {
                    sLengthMenu: "_MENU_",
             },  
			 "drawCallback": function (settings, json) {
                $('[data-toggle="tooltip"]').tooltip('update');
            },
			 "beforeSend": function() {
                $(".loader").show();
            },
            "complete": function() {
                $(".loader").hide();
            },
       
            "ajax":{
                "url": "<?php echo base_url('common/get_reminder'); ?>",
                "dataType": "json",
                "type": "POST",
                "data": function(data){
					var date_range="<?php if(isset($_GET['date_range']))   { echo $_GET['date_range']; }?>";
                   	var keywords="<?php if(isset($_GET['keywords'])) { echo $_GET['keywords']; }?>";		
                    data.keywords   = keywords;		
                    data.status   = '<?= $status;?>';		
                }
                  
            },               
            "columns": [
                { "data": "sr_no" },
                { "data": "title" },
                { "data": "description" },
                { "data": "reminder_date" },
                { "data": "date" },
				<?php if ($status == 'done'){?>
                { "data": "done_date" },
				<?php } ?>
                { "data": "action" },
            ], 
            
            "buttons": [
                {
                    "extend": 'excel',
                    "text": '<button class="btn btn-success waves-effect waves-float waves-light"><i class="fa fa-file-excel-o"></i>  Excel</button>',                  
				   <?php if ($status == 'pending'){?>
					  "exportOptions": {
						"columns": [0,1,2,3,4]
					  }	
					<?php } else{?>
					  "exportOptions": {
						"columns": [0,1,2,3,4,5]
					  }	
					<?php } ?>
                },
                {
                    "extend": 'pdfHtml5',
                    "orientation": 'landscape',
                    "text": '<button class="btn btn-danger waves-effect waves-float waves-light"><i class="fa fa-file-pdf-o"></i> PDF</button>',  
				   <?php if ($status == 'pending'){?>
					  "exportOptions": {
						"columns": [0,1,2,3,4]
					  }	
					<?php } else{?>
					  "exportOptions": {
						"columns": [0,1,2,3,4,5]
					  }	
					<?php } ?>
               }
             ], 
            
            "infoCallback": function( settings, start, end, max, total, pre ) {
               $(".loader").fadeOut("slow"); 
               $('#total_count').html('('+total+')');
               return 'Showing ' +start+ ' to ' + end + ' of '+ total + ' entries';
            }, 
            
            'columnDefs': [
                {
                    "targets": 0, // your case first column
                    "className": "text-center",
                  
               },
                {
                    "targets": 5, // your case first column
                    "className": "text-center",
                  
               },
            ] 
            /*"columnDefs": [{
                targets: "_all",
                orderable: false
             }]*/
        });  
        
   
    });
  </script>
  
