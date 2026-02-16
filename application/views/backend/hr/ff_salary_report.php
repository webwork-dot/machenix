<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/datatables.buttons.min.js"></script>

<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/jszip.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/pdfmake.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/vfs_fonts.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/buttons.html5.min.js"></script>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/fixedcolumns/4.1.0/css/fixedColumns.dataTables.min.css">
<script src="https://cdn.datatables.net/fixedcolumns/4.1.0/js/dataTables.fixedColumns.min.js"></script>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/fixedheader/3.4.0/css/fixedHeader.dataTables.min.css">

<script src="https://cdn.datatables.net/fixedheader/3.4.0/js/dataTables.fixedHeader.min.js"></script>

<style>
.table thead th {
    padding: 10px 6px;
}
.m-deduct {
    width: 80px;
    border-radius: 0px;
    height: auto;
    font-size: 13px;
    padding: 6px 6px;
}
</style>

<div class="row" id="table-bordered">
   <div class="col-12">
      <div class="card">
       
         <?php include '_filter_generate_ff_salary.php';?>
         
         <div class="card-body">
            <div class="row">
               <div class="col-md-12 mt-10">
                  <h5 class="mb-0"><b>Total Data <span id="total_count"> (0)</span></b></h5>
               </div>
            </div>
         </div>
         <div class="card-datatable d-report mb-2">
          <div class="card-body">           
            <table class="table m-report" id="flash-datatable">
               <thead>
                  <tr>                                    
                    <th>#</th>
                    <th>BANK</th>
                    <th>Bank A/C No</th>
                    <th>EMP NAME</th>
                    <th>DAYS OF MONTH</th>
					
					<th>WORKING DAYS</th>					 
                    <th>TOTAL CALLS</th>
                    <th>CALLS DONE</th>
                    <th>TOTAL DSS</th>
                    <th>TOTAL CAMP</th>
                    <th>CALLS DONE 
					<br> <small>AFTER DSS & CAMP</small></th>
					
					
					
                    <th>PAID LEAVE</th>
                    <th>PRESENT DAY</th>
                    <th>ABSENT DAY</th>
                    <th>BASIC SALARY</th>
                    <th>H.R.A</th>
                    <th>GROSS EDU. ALLOW</th>
                    <th>GROSS PACKAGE</th>
                    <th>GROSS SALARY EARNED</th>
                    <th>LOANS/ADVANCE TAKEN</th>
                    <th>MOBILE LOAN TAKEN</th>  
                    <th>ADJUSTMENT<br><small>(ARREARS /DEDUCTION)</small></th>  
					<th>LOANS/ADVANCE<br><small> DEDUCTION</small></th>
					<th>MOBILE LOAN <br><small> DEDUCTION</small> </th>
                    <th>T.D.S. TAX</th>
                    <th>P.F</th>
                    <th>P.TAX</th>
                    <th>ESIC</th>                
                    <th>TOTAL DEDUCTION AMT</th>
                    <th>FINAL SALARY</th>
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
        var dataTable = $('#flash-datatable').DataTable({ 
            "dom": '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6" l B><"col-sm-12 col-md-6">>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',     "ordering": false,
            "sDom": 'rt<"dtPagination"lp><"clear">',
            "pagingType": "simple_numbers",
            "processing": true,
            'scrollX': true,
            "serverSide": true,  
			"pageLength":250,
			"fixedColumns": {
				"left": 2,
			},
			"scrollCollapse": true,
			"scrollX": true,
			"scrollY": 500, 
			"fixedHeader": true, 
			"fixedHeader": {
				"headerOffset": 82
			},
            "lengthChange": true,  
			"lengthMenu": [50, 100, 250,500],
            "language" : {
                sLengthMenu: "_MENU_",
                'processing': $('.loader').show()
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
                "url": "<?php echo base_url('attendance/get_generated_ff_salary_report'); ?>",
                "dataType": "json",
                "type": "POST",
                "data": function(data){
                    data.month_id = "<?php if(isset($_GET['month_id'])) { echo $_GET['month_id']; }?>";	      		
                    data.salary_type = "<?php if(isset($_GET['salary_type'])) { echo $_GET['salary_type']; }?>";			
                }
            },   
                    
            "columns": [
                { "data": "sr_no" }, 
                { "data": "bank" },  
                { "data": "account_no" },  
                { "data": "name" },  
                { "data": "day_of_month" },
				
                { "data": "working_days" }, 
                { "data": "total_calls" }, 
                { "data": "calls_done" }, 
                { "data": "total_dss" }, 
                { "data": "total_camp" }, 
                { "data": "calls_after_dss_camp" }, 
				
                { "data": "paid_leave" },  
                { "data": "present_day" },  
                { "data": "absent_day" },  
                { "data": "basic_salary" },
                { "data": "hra" },
                { "data": "gross_edu" },
                { "data": "gross_package" },
                { "data": "gross_salary_earned" },
                { "data": "loans_advances" },
                { "data": "mobile_loan" },
                { "data": "adjustment" },
                { "data": "loan_deduction" },
                { "data": "mobile_deduction" },
                { "data": "tds" },
                { "data": "pf" },
                { "data": "p_tax" },
                { "data": "esic" },
                { "data": "total_deduction" },
                { "data": "final_salary" },
            ], 
           
            "buttons": [
                {
                    "extend": 'excel',
                    "text": '<button class="btn btn-success waves-effect waves-float waves-light"><i class="fa fa-file-excel-o"></i>  Excel</button>',
                },
            
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
             
            ],
		rowCallback: function (row, data) {
            $(row).addClass(data.class_name);       
        }			
						
	   }).on('draw.dt', function () { 
		 $(".loader").fadeOut("slow"); 
	  }); 
    });
</script>
