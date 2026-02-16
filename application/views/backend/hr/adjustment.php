<link rel="stylesheet" type="text/css" href="<?= base_url();?>app-assets/vendors/css/tables/datatable/dataTables.bootstrap5.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/fixedcolumns/4.1.0/css/fixedColumns.dataTables.min.css">
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/fixedcolumns/4.1.0/js/dataTables.fixedColumns.min.js"></script>

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
            <h4 class="mb-0"><i class="feather icon-filter"></i> Filter</h4>
         </span>
      </div>
      <div id="collapseOne" class="collapse show pb-1" aria-labelledby="headingOne" data-parent="#accordionFilter">
         <section class="filter-section">
            <div class="row match-height">
               <div class="col-12">
                  <div class="card mb-0">
                     <div class="card-content">
                        <div class="card-body">
                           <form class="form form-vertical" id="form_filter" method="GET">
                              <input type="hidden" name="filter" value="">
                              <div class="form-body">
                                 <div class="row">
                                  <div class="col-md-4 col-12">
									<label>Select Month/Year</label>
									<div class="input-group">
										<select class="form-select" name="month">
										<option value="" <?php if($_GET['month']==''){ echo 'selected';}?>>Select Month</option>
										<option value="01" <?php if($_GET['month']=='01'){ echo 'selected';}?>>January</option>
										<option value="02" <?php if($_GET['month']=='02'){ echo 'selected';}?>>February</option>
										<option value="03" <?php if($_GET['month']=='03'){ echo 'selected';}?>>March</option>
										<option value="04" <?php if($_GET['month']=='04'){ echo 'selected';}?>>April</option>
										<option value="05" <?php if($_GET['month']=='05'){ echo 'selected';}?>>May</option>
										<option value="06" <?php if($_GET['month']=='06'){ echo 'selected';}?>>June</option>
										<option value="07" <?php if($_GET['month']=='07'){ echo 'selected';}?>>July</option>
										<option value="08" <?php if($_GET['month']=='08'){ echo 'selected';}?>>August</option>
										<option value="09" <?php if($_GET['month']=='09'){ echo 'selected';}?>>September</option>
										<option value="10" <?php if($_GET['month']=='10'){ echo 'selected';}?>>October</option>
										<option value="11" <?php if($_GET['month']=='11'){ echo 'selected';}?>>November</option>
										<option value="12" <?php if($_GET['month']=='12'){ echo 'selected';}?>>December</option>
									</select>
									<select class="form-select" name="year">
										<option value="">Select Year</option>
										<?php
										$currentYear = CURREN_YEAR;
										for ($i = $currentYear; $i <= date('Y'); $i++) {
											$selected = ($_GET['year'] == $i) ? 'selected' : '';
											echo "<option value='$i' $selected>$i</option>";
										}
										?>
									</select>
								</div>
                               </div>   

                                 <div class="col-md-4 col-12">
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
                                  <button type="submit" name="search" value="true" id="search"  class="btn btn-primary mr-1 mb-0">Search</button>
                                  <?php if(isset($_GET['filter'])):?>
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
      


<div class="row" id="table-bordered">
   <div class="col-12">
      <div class="card">
         <div class="card-body">
            <div class="row">
               <div class="col-md-12 mt-10">
                  <h5 class="mb-0"><b>Total Adjustment Data <span id="total_count"> (0)</span></b></h5>
               </div>
            </div>
         </div>
         <div class="card-datatable d-report mb-2"> 
		 <a href="<?php echo site_url('hr/adjustment/add'); ?>" class="dt-button add-new  add-btn btn btn-primary" tabindex="0" aria-controls="DataTables_Table_0" ><span><i data-feather='plus'></i> Add Adjustment</span></a>  
          <div class="card-body pt-0"> 
            <table class="table leads-table tfixed" id="repots-datatable">
               <thead>
                  <tr>                                    
                    <th style="width:35px">#</th>
					<th style="width:150px">Employee</th>
					<th style="width:90px">Amount</th>
					<th style="width:160px">Deduction On Salary</th>
					<th style="width:80px">Status</th>
					<th style="width:115px">Added By </th>
					<th style="width:145px">Added Date</th>
					<th style="width:90px">Actions</th>
                  </tr>
               </thead>
            </table>
         </div>
         </div>
      </div>
     </div>
   </div>



<script type="text/javascript"> 
  $(document).ready(function($) { 
        var dataTable = $('#repots-datatable').DataTable({ 
            "dom": '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6 no-padd"l B><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            "ordering": false,
            "sDom": 'rt<"dtPagination"lp><"clear">',
            "pagingType": "simple_numbers",
            "processing": true,
            'scrollX': true,
            "serverSide": true, 
            "searching": false, 
			"pageLength": 10,
            "lengthChange": true,  
            "lengthMenu": [10,25, 50, 100, 250],
            "language" : {
                    sLengthMenu: "_MENU_",
                    'processing': $('.loader').show()
             },
            "drawCallback": function (settings, json) {
                $('[data-toggle="tooltip"]').tooltip('update');
            },
        
            "ajax":{
                "url": "<?php echo base_url('attendance/get_adjustment'); ?>",
                "dataType": "json",
                "type": "POST",
                "data": function(data){   
 			 	 data.month="<?php if(isset($_GET['month'])) { echo $_GET['month']; }?>";
 				 data.year = "<?php if(isset($_GET['year'])) { echo $_GET['year']; }?>";
                 data.keywords = "<?php if(isset($_GET['keywords'])) { echo $_GET['keywords']; }?>";				
                }
                
            },     
            "columns": [
			    { "data": "sr_no" },
                { "data": "emp_name" },
                { "data": "amount" },
                { "data": "adjustment_deduction" },
                { "data": "status" },
                { "data": "added_by_name" },
                { "data": "date" },
                { "data": "action" },
            ], 
            
           "buttons": [
                {
                    "extend": 'excel',
                    "text": '<button class="btn btn-success waves-effect waves-float waves-light"><i class="fa fa-file-excel-o"></i>  Excel</button>',
                    "exportOptions": {
                       "columns": [0,1,2,3,4,5,6,7]
                    }
                },
                {
                    "extend": 'pdfHtml5',
                    "orientation": 'landscape',
                    "text": '<button class="btn btn-danger waves-effect waves-float waves-light"><i class="fa fa-file-pdf-o"></i> PDF</button>',  
                    "exportOptions": {
                       "columns": [0,1,2,3,4,5,6,7]
                    }
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
            ] 
            /*"columnDefs": [{
                targets: "_all",
                orderable: false
             }]*/
        }).on('draw.dt', function () { 
            $(".loader").fadeOut("slow"); 
        });   
        
   
    });  
  </script>
