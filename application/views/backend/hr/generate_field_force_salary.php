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
    display: inline-block;
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
                    <th>EMP NAME</th>
                    <th>DAYS OF MONTH</th>
                    <th>WORKING DAYS</th>
                    <th>TOTAL CALLS</th>
                    <th>CALLS DONE</th>
                    <th>TOTAL DSS</th>
                    <th>TOTAL CAMP</th>
                    <th>CALLS DONE <i class="feather icon-help-circle" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Non Sunday Camp Or DSS added 10 Calls per Camp and DSS"></i>
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
                    <th>FINAL SALARY </th>
                    <th>ACTION </th>
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
            "dom": '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6">>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',     "ordering": false,
            "sDom": 'rt<"dtPagination"lp><"clear">',
            "pagingType": "simple_numbers",
            "processing": true,
            'scrollX': true,
            "serverSide": true,  
			"pageLength": 10,
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
			"lengthMenu": [10,25, 50, 100, 250,500],
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
                "url": "<?php echo base_url('attendance/get_field_force_salary_report_list'); ?>",
                "dataType": "json",
                "type": "POST",
                "data": function(data){
                    data.month_id = "<?php if(isset($_GET['month_id'])) { echo $_GET['month_id']; }?>";	 			
                    data.keywords = "<?php if(isset($_GET['keywords'])) { echo $_GET['keywords']; }?>";	 			
                }
            },			
            "columns": [
                { "data": "sr_no" }, 
                { "data": "name" },  
                { "data": "day_of_month" },  
                { "data": "working_days" },  
                { "data": "total_calls" },  
                { "data": "calls_done" },  
                { "data": "total_dss" },  
                { "data": "total_camp" },  
                { "data": "calls_after_dss_camp"},
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
                { "data": "action" },
            ], 
           
            "buttons": [
                {
                    "extend": 'excel',
                    "text": '<button class="btn btn-success waves-effect waves-float waves-light"><i class="fa fa-file-excel-o"></i>  Excel</button>',
                },
                {
                    "extend": 'pdfHtml5',
                    "orientation": 'landscape',
                    "text": '<button class="btn btn-danger waves-effect waves-float waves-light"><i class="fa fa-file-pdf-o"></i> PDF</button>',  
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
             
            ],
		rowCallback: function (row, data) {
            $(row).addClass(data.class_name);       
        }			
			
			
			
            
        }).on('draw.dt', function () { 
            $(".loader").fadeOut("slow"); 
        }); 

		
    });
	
  function calculate_pf(new_basic_salary, new_gross_edu) {	
      $.ajax({
        url: "<?php echo site_url('attendance/get_calculate_pf'); ?>",
        type: "POST",
        data: {
          param1: new_basic_salary,
          param2: new_gross_edu,
          param3: '',
        },
        dataType: "json",
        success: function(data) {
          // Update the result div with the response from the controller
          //$("#result").text(data);
		  //console.log(data);
		  return data;
        },
        error: function(xhr, status, error) {
          console.error(error);
        }
      });
    
}
	
function calculate_esic(new_basic_salary, new_hra, new_gross_edu){	
      $.ajax({
        url: "<?php echo site_url('attendance/get_calculate_esic'); ?>",
        type: "POST",
        data: {
          param1: new_basic_salary,
          param2: new_hra,
          param3: new_gross_edu,
        },
        dataType: "json",
        success: function(data) {
          // Update the result div with the response from the controller
          //$("#result").text(data);
		  //console.log(data);
		  return data;
        },
        error: function(xhr, status, error) {
          console.error(error);
        }
      });
    
}

function update_price() {
  var data_id = $(this).data("id"); 

   var paid_leave 	 	= $('#paid_leave_'+data_id).val();  

   var absent_days 	 	= $('#absent_input_'+data_id).val();  
   var present_days 	= $('#present_input_'+data_id).val();  
   
   
   var adjustment 	    = $('#adjustment_'+data_id).val();
   var loan_deduction   = $('#loan_deduction_'+data_id).val();
   var mobile_deduction = $('#mobile_deduction_'+data_id).val();
   var tds 				= $('#tds_'+data_id).val(); 
   var total_deduction 	= 0;
  
   //leave calculation   
  if(paid_leave>absent_days){
	  alert('Paid Leave must be less than or equal to Absent');
      $('#paid_leave_'+data_id).val(0); 
	  $('#present_'+data_id).html(present_days); 
	  return false;
  }
  else{	  
    var day_of_month= $('#day_of_month_'+data_id).val(); 
    var gross_package= $('#gross_package_'+data_id).val(); 
    var is_pf= $('#is_pf_'+data_id).val(); 
    var is_esic= $('#is_esic_'+data_id).val(); 
			  
    // Calculate 25%-50% amount
	var hra=gross_edu=basic_salary=0;
    var twentyFivePercent = gross_package * 0.25;
    var fiftyPercent = gross_package * 0.5;	
	var new_hra=new_gross_edu=new_basic_salary=0;
    new_hra=twentyFivePercent.toFixed(2);
    new_gross_edu=twentyFivePercent.toFixed(2);
    new_basic_salary=fiftyPercent.toFixed(2);
	
	var gross_salary_earned=0;
	var total_present_days=Number(present_days) + Number(paid_leave);
	gross_salary_earned = (Number(gross_package)/Number(day_of_month))*Number(total_present_days);

	$('#gross_salary_earned_'+data_id).html(Math.round(gross_salary_earned)); 
    // console.log('gross_package'+gross_package); 
   // console.log('total_present_days'+total_present_days); 
    //console.log('gross_salary_earned'+gross_salary_earned); 
	
    if(is_pf==1){
	  var pf=calculate_pf(new_basic_salary, new_gross_edu);
	  $('#pf_'+data_id).html(pf); 
    }
    else{
	  $('#pf_'+data_id).html(0); 
    } 
 
    if(is_esic==1){
	   var esic=calculate_esic(new_basic_salary, new_hra, new_gross_edu);
	   $('#esic_'+data_id).html(esic); 	   
    }
    else{
	  $('#esic_'+data_id).html(0); 
    }
    
    $('#present_'+data_id).html(parseFloat(Number(paid_leave)+Number(present_days))); 
 
   var pf 	 = parseFloat($('#pf_'+data_id).text());
   var p_tax = parseFloat($('#p_tax_'+data_id).text());
   var esic  = parseFloat($('#esic_'+data_id).text());
	
    total_deduction=Number(loan_deduction) + Number(mobile_deduction) + Number(tds)+ Number(pf) + Number(p_tax) + Number(esic);
 
	var final_salary=0;
	final_salary=Number(gross_salary_earned) -  Number(total_deduction) + Number(adjustment);
    //console.log('present_days'+total_present_days); 
    //console.log('paid_leave'+paid_leave); 
    //console.log('gross_salary_earned'+gross_salary_earned); 
	
    $('#final_salary_'+data_id).html(Math.round(final_salary)); 
    $('#total_deduction_'+data_id).html(Math.round(total_deduction)); 
   
  }
	
}

	

$(document).ready(function () {
  $('#flash-datatable').on('keyup', '.m-deduct', function () {
    update_price.call(this);
  }); 


  $('#flash-datatable').on('click', '.btn-generate-salary', function () {	  
	var data_id = $(this).data("id");		
	var name  	= $('#name_' + data_id).val();
	  
      var confirmDlg = duDialog(null, "Are you sure you want to Generete Salary of "+name+"?", {
        init: true,
        dark: false, 
        buttons: duDialog.OK_CANCEL,
        okText: 'Proceed',
        callbacks: {
          okClick: function(e) {
            $(".dlg-actions").find("button").attr("disabled",true);
            $(".ok-action").html('<i class="fa fa-spinner fa-pulse"></i> Please wait!');
            $(".loader").show();  
			
		    $('#btn_generate_'+data_id).html('<i class="fa fa-spinner fa-spin" aria-hidden="true" style="font-size: 14px;color: #fff;"></i> processing'); 
			$('#btn_generate_'+data_id).attr("disabled", true);	
			
			var paid_leave  	= $('#paid_leave_' + data_id).val();	
		    var absent_days 	= $('#absent_input_'+data_id).val();  
			var adjustment  	= $('#adjustment_' + data_id).val();	
			var loan_deduction  = $('#loan_deduction_' + data_id).val();	
			var mobile_deduction= $('#mobile_deduction_' + data_id).val();	
			var tds  			= $('#tds_' + data_id).val();	
			var emp_id  		= $('#emp_id_' + data_id).val();	
			var month_id  		= $('#month_' + data_id).val();	
			
		   if(paid_leave>absent_days){
			  $(".loader").fadeOut("slow"); 
			   $('#btn_generate_'+data_id).html('<i class="fa fa-refresh"></i> Generate Salary');
			   $('#btn_generate_'+data_id).attr("disabled", false)
			   alert('Paid Leave must be less than or equal to Absent'); 
			   return false;
		    }
			else if(paid_leave==''){
			  $(".loader").fadeOut("slow"); 
			  $('#btn_generate_'+data_id).html('<i class="fa fa-refresh"></i> Generate Salary');
			  $('#btn_generate_'+data_id).attr("disabled", false)
			  alert('Error! Paid leave can not be blank!');
			  return false;				  
			}  			
			else if(adjustment==''){
			  $(".loader").fadeOut("slow"); 
			  $('#btn_generate_'+data_id).html('<i class="fa fa-refresh"></i> Generate Salary');
			  $('#btn_generate_'+data_id).attr("disabled", false)
			  alert('Error! Adjustment can not be blank!');
			  return false;				  
			}  		
			else if(loan_deduction==''){
			  $(".loader").fadeOut("slow"); 
			  $('#btn_generate_'+data_id).html('<i class="fa fa-refresh"></i> Generate Salary');
			  $('#btn_generate_'+data_id).attr("disabled", false)
			  alert('Error! Loan Deduction can not be blank!');
			  return false;				  
			}
			else if(mobile_deduction==''){
			  $(".loader").fadeOut("slow"); 
			  $('#btn_generate_'+data_id).html('<i class="fa fa-refresh"></i> Generate Salary');
			  $('#btn_generate_'+data_id).attr("disabled", false)
			  alert('Error! Mobile Deduction can not be blank!');
			  return false;				  
			} 	
			else if(tds==''){
			  $(".loader").fadeOut("slow"); 
			  $('#btn_generate_'+data_id).html('<i class="fa fa-refresh"></i> Generate Salary');
			  $('#btn_generate_'+data_id).attr("disabled", false)
			  alert('Error! TDS can not be blank!');
			  return false;				  
			}            
			else{ 	 		
			 $.ajax({
				url : "<?php echo base_url('attendance/generate_salary');?>",
				method : "POST",
				async: true,
				dataType: 'json',
				data : {month_id:month_id,type:'FIELD-STAFF',paid_leave: paid_leave,adjustment:adjustment,loan_deduction:loan_deduction,mobile_deduction:mobile_deduction,tds:tds,id:data_id,emp_id:emp_id}				
			})
			 .done(function(res) {
              confirmDlg.hide();
    		 if (res.status == '200') {  
					  $(".loader").fadeOut("slow"); 
					  Swal.fire({
						title: "Success!",
						text: res.message,
						icon: "success",
						customClass: {
							confirmButton: "btn btn-primary"
						},
						buttonsStyling: !1
					  }).then(() => {window.location.href = res.url;});                         
					}
					else { 
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
					    $('#btn_generate_'+data_id).html('<i class="fa fa-refresh"></i> Generate Salary');
						$('#btn_generate_'+data_id).attr("disabled", false);
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
				$('#btn_generate_'+data_id).html('<i class="fa fa-refresh"></i> Generate Salary');
				$('#btn_generate_'+data_id).attr("disabled", false);
            });		
			
		 return false;		
	     }
        			
         }
        }
      });
      confirmDlg.show();
		 
	 
  });
	
	
	
	

  $('#flash-datatable').on('click', '.btn-hold-salary', function () {	  
		var data_id = $(this).data("id");		
		var name  	= $('#name_' + data_id).val();	
		var emp_id  		= $('#emp_id_' + data_id).val();	
		var month_id  		= $('#month_' + data_id).val();	
			
      var confirmDlg = duDialog(null, "Are you sure you want to Hold Salary of "+name+"?", {
        init: true,
        dark: false, 
        buttons: duDialog.OK_CANCEL,
        okText: 'Proceed',
        callbacks: {
          okClick: function(e) {
            $(".dlg-actions").find("button").attr("disabled",true);
            $(".ok-action").html('<i class="fa fa-spinner fa-pulse"></i> Please wait!');
            $(".loader").show();  
			
		    $('#btn_hold_'+data_id).html('<i class="fa fa-spinner fa-spin" aria-hidden="true" style="font-size: 14px;color: #fff;"></i> processing'); 
			$('#btn_hold_'+data_id).attr("disabled", true);	
				 		
			 $.ajax({
				url : "<?php echo base_url('attendance/hold_salary');?>",
				method : "POST",
				async: true,
				dataType: 'json',
				data : {month_id:month_id,type:'FIELD-STAFF',id:data_id,emp_id:emp_id}				
			})
			 .done(function(res) {
              confirmDlg.hide();
    		 if (res.status == '200') {  
					  $(".loader").fadeOut("slow"); 
					  Swal.fire({
						title: "Success!",
						text: res.message,
						icon: "success",
						customClass: {
							confirmButton: "btn btn-primary"
						},
						buttonsStyling: !1
					  }).then(() => {window.location.href = res.url;});                         
					}
					else { 
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
					    $('#btn_hold_'+data_id).html('<i class="fa fa-pause"></i> Hold Salary');
						$('#btn_hold_'+data_id).attr("disabled", false);
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
				$('#btn_hold_'+data_id).html('<i class="fa fa-pause"></i> Hold Salary');
				$('#btn_hold_'+data_id).attr("disabled", false);
            });		
			
		 return false;		
	     
        			
         }
        }
      });
      confirmDlg.show();
		 
	 
  });
	
});


	
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
