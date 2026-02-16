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



<div class="row" id="table-bordered">
   <div class="col-12">
      <div class="card">

        <div class="card-body border-bottom pb-5">
           <form class="form form-vertical" id="form_filter" method="GET">
              <input type="hidden" name="filter" value="">
              <div class="form-body">
                 <div class="row">			          
				  <div class="col-md-4 col-12">
					  <div class="form-group mb-0">
						<label>Staff Type</label>
						<select class="form-select" name="staff_type">
							<option value="" >Select Staff Type</option>
							 <?php 
							 $staff_types=$this->hr_model->get_filter_staff_type();
							 foreach($staff_types as $stype){?>
							 <option value="<?php echo $stype['id'];?>" <?php if($this->input->get('staff_type') == $stype['id']){ echo 'selected';}?>><?php echo $stype['name'];?></option>
							 <?php }?>
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
                      <button type="submit" name="search" value="true" id="search"  class="btn btn-outline-dark  mr-1 mb-0">Search</button>
                      <?php if(isset($_GET['filter'])):?>
                      <a href="<?php echo currentUrl($_SERVER["REQUEST_URI"]);?>"><button type="button" class="btn btn-outline-danger mr-1 mb-0 " id="show">Reset</button> </a>
                     <?php endif;?>
                   </div>
                 </div>
                 
                 </div>
              </div>
           </form>
        </div>
     
	  
	  
         <div class="card-body">
            <div class="row">
               <div class="col-md-12 mt-10">
                  <h5 class="mb-0"><b>Total Loans Data <span id="total_count"> (0)</span></b></h5>
               </div>
            </div>
         </div>
         <div class="card-datatable d-report mb-2"> 
		 <a href="<?php echo site_url('hr/loans/add'); ?>" class="dt-button add-new  add-btn btn btn-primary" tabindex="0" aria-controls="DataTables_Table_0" ><span><i data-feather='plus'></i> Add Loans</span></a>  
          <div class="card-body pt-0"> 
            <table class="table leads-table tfixed" id="repots-datatable">
               <thead>
                  <tr>                                    
                    <th style="width:35px">#</th>
					<th style="width:130px">Employee</th>
					<th style="width:130px">Staff Type</th>
					<th style="width:90px">Loan Type</th>
					<th style="width:50px">Instl</th>
					<th style="width:90px">Amount</th>
					<th style="width:90px">EMI</th>
					<th style="width:90px">Amount Paid</th>
					<th style="width:90px">Balance</th>
					<th style="width:80px">Status</th>
					<th style="width:115px">Added By </th>
					<th style="width:120px">Added Date</th>
					<th style="width:140px">Actions</th>
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
		   "dom": '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l B><"col-sm-12 col-md-6">>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12     col-md-6"p>>',  
			"ordering": false,
            "sDom": 'rt<"dtPagination"lp><"clear">',
            "pagingType": "simple_numbers",
            "processing": true,
            'scrollX': true,
            "serverSide": true, 
            "lengthChange": true,  
            "language" : {
                sLengthMenu: "_MENU_",
                'processing': $('.loader').show()
            },
            "drawCallback": function (settings, json) {
                $('[data-toggle="tooltip"]').tooltip('update');
            },
        
            "ajax":{
                "url": "<?php echo base_url('attendance/get_loans'); ?>",
                "dataType": "json",
                "type": "POST",
                "data": function(data){              
                    data.salary_type = "<?php if(isset($_GET['salary_type'])) { echo $_GET['salary_type']; }?>";				
                    data.keywords = "<?php if(isset($_GET['keywords'])) { echo $_GET['keywords']; }?>";				
                }
                
            },     
            "columns": [
			    { "data": "sr_no" },
                { "data": "emp_name" },
                { "data": "salary_type" },
                { "data": "loan_type" },
                { "data": "instalment" },
                { "data": "amount" },
                { "data": "emi" },
                { "data": "amount_paid" },
                { "data": "balance" },
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
                       "columns": [0,1,2,3,4,5,6,7,8,9,10,11]
                    }
                },
                {
                    "extend": 'pdfHtml5',
                    "orientation": 'landscape',
                    "text": '<button class="btn btn-danger waves-effect waves-float waves-light"><i class="fa fa-file-pdf-o"></i> PDF</button>',  
                    "exportOptions": {
                       "columns": [0,1,2,3,4,5,6,7,8,9,10,11]
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
