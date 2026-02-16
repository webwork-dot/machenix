<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/datatables.buttons.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/jszip.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/pdfmake.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/vfs_fonts.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/buttons.html5.min.js"></script>

<style>
.attn-summary{   
    display: flex;
    align-items: center;
    justify-content: flex-end;
}
.attn-summary h5 {
    margin-right: 14px;
    font-size: 15px!important;
    border: 1px dashed #1e652e;
    padding: 6px 7px;
    color: #1e652e;
}
.paging_simple_numbers{ display:none; }
.text-right{ text-align: right;}
</style>

<div class="row" id="table-bordered">
   <div class="col-12">
      <div class="card">
       
         <?php include '_filter_attendance.php';?>
         
         <div class="card-body">
            <div class="row">
               <div class="col-md-2 mt-10">
                  <h5 class="mb-0"><b>Total Days	
				  <span id="total_count"> (0)</span></b></h5>
               </div>

			   <div class="col-md-10 mt-10">
		
			   
			    <div class="attn-summary">
                  <h5 class="mb-0"><b><span id="xshift">N/A</span></b></h5>
                  <h5 class="mb-0"><b>Present - <span id="presentCount"> 0</span></b></h5>
                  <h5 class="mb-0"><b>Absent - <span id="absentCount"> 0</span></b></h5>
                  <h5 class="mb-0"><b>Late - <span id="lateCount"> 0</span></b></h5>
                  <h5 class="mb-0"><b>Half Day - <span id="halfDayCount"> 0</span></b></h5>
                  <h5 class="mb-0"><b>Weekly Off  - <span id="weeklyOffCount"> 0</span></b></h5>
                  <h5 class="mb-0"><b>Holiday  - <span id="holidayCount"> 0</span></b></h5>
				 
				 <?php
					$rules_url= site_url('modal/popup_admin_role/modal_attendance_rules/common');
					$rules_btn='<a href="javascript:void(0);" onclick="showAjaxModal(\''.$rules_url.'\', \'Attendance Rules\')" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Attendance Rules"><button type="button" class="btn mr-1 mb-1 icon-btn-del"><b><i class="fa fa-info-circle"></i> Rules</b></button></a>';
					echo $rules_btn;
				   ?>
                </div>
               </div>	
            </div>
         </div>
		 
		   
	 <?php echo form_open('#', ['class' => 'add-ajax-redirect-form', 'enctype' => 'multipart/form-data', 'onsubmit' => 'return checkForm(this);' ]);?>
  
		 <input type="hidden" name="emp_id" value="<?php if(isset($_GET['emp_id'])) { echo $_GET['emp_id']; }?>">
		 <input type="hidden" name="month_id" value="<?php if(isset($_GET['month_id'])) { echo $_GET['month_id']; }?>">
		 <input type="hidden" name="year" value="<?php if(isset($_GET['year'])) { echo $_GET['year']; }?>">
		   
         <div class="card-datatable d-report mb-2" id="action-datatable">
          <div class="card-body pt-0">           
            <table class="table leads-table tfixed" id="flash-datatable">
               <thead>
                  <tr>
                                    
                    <th style="width:40px">Sr No</th>
                    <th style="width:80px">Date</th>
                    <th style="width:80px">Day</th>
                    <th style="width:80px">Check In</th>
                    <th style="width:100px">Check Out</th>
                    <th style="width:100px">Total Hrs</th>
                    <th style="width:100px">Status</th>
                    <th style="width:100px">Action</th>
                  </tr>
               </thead>
            </table>
         </div>
		 
	 
	   <div class="col-12 px-1 mt-2 mb-2 text-right">
			<button type="button" class="mb-2 dt-button add-new btn btn-primary waves-effect waves-float waves-light me-1 btnf btn_bulk_apply pull-right" name="btn_bulk_apply"> <i class="fa fa-refresh"></i> Update Attendance</button>
	   </div>
	   </div>
      <?php echo form_close(); ?>	 
		 
      </div>
     </div>
</div>

          

<script type="text/javascript">
    $(document).ready(function($) {
        var dataTable = $('#flash-datatable').DataTable({ 
            "dom": '<"d-flex justify-content-between align-items-center mx-0 mb-2 row"<"col-sm-12 col-md-6"l B><"col-sm-12 col-md-6">>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',     "ordering": false,
            "sDom": 'rt<"dtPagination"lp><"clear">',
            "pagingType": "simple_numbers",
            "processing": true,
            'scrollX': true,
            "serverSide": true,  
			"pageLength": 100, 
            "lengthChange": false, 
			"info": false,
            "language" : {
                sLengthMenu: "_MENU_",
                'processing': $('.loader').show()
            },  
			"beforeSend": function() {
                $(".loader").show();
            },
            "complete": function() {
                $(".loader").hide();
            },
            "drawCallback": function (settings, json) {
                $('[data-toggle="tooltip"]').tooltip('update');
            },
            "ajax":{
                "url": "<?php echo base_url('attendance/get_attendance_list'); ?>",
                "dataType": "json",
                "type": "POST",
                "data": function(data){
                    data.month_id = "<?php if(isset($_GET['month_id'])) { echo $_GET['month_id']; }?>";	               
                    data.year = "<?php if(isset($_GET['year'])) { echo $_GET['year']; }?>";	               
                    data.emp_id = "<?php if(isset($_GET['emp_id'])) { echo $_GET['emp_id']; }?>";			
                    data.salary_type = "<?php if(isset($_GET['salary_type'])) { echo $_GET['salary_type']; }?>";			
                },
				dataSrc: function(response) {
					// Access the counts for different statuses
					var absentCount = response['Absent'] || 0;
					var halfDayCount = response['Half Day'] || 0;
					var lateCount = response['Late'] || 0;
					var presentCount = response['Present'] || 0;
					var weeklyOffCount = response['Weekly Off'] || 0;
					var holidayCount = response['Holiday'] || 0;
					var xshift = response['shift_type'] || 'N/A';
					var total_count = response['recordsTotal'] || 0;
					
					$('#xshift').html(xshift);
					$('#absentCount').html(absentCount);
					$('#halfDayCount').html(halfDayCount);
					$('#lateCount').html(lateCount);
					$('#presentCount').html(presentCount);
					$('#weeklyOffCount').html(weeklyOffCount);
					$('#holidayCount').html(holidayCount);
					$('#total_count').html('('+total_count+')');
 

					// Return the main data (table rows) to DataTables for processing
					return response.data;
				  },
    
            },   
                    
            "columns": [
                { "data": "sr_no" }, 
                { "data": "punch_date" },  
                { "data": "day" },  
                { "data": "check_in_date" },  
                { "data": "check_out_date" },  
                { "data": "total_hrs" },
                { "data": "status" },
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
             
            ] 
            
        }).on('draw.dt', function () { 
            $(".loader").fadeOut("slow"); 
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
	
	
	
	
	

$(document).ready(function () {

  $('#action-datatable').on('click', '.btn_bulk_apply', function () {	 
	var name  	= $("#sel_empid option:selected").text();;
	var month 	= $("#sel_monthid option:selected").text();
	var year 	= $("#sel_year option:selected").text();
	var month_label = month+'-'+year;
	  
      var confirmDlg = duDialog(null, "Are you sure you want to update the attendance status of <b>"+name+" for "+month_label+"?</b>", {
        init: true,
        dark: false, 
        buttons: duDialog.OK_CANCEL,
        okText: 'Proceed',
        callbacks: {
          okClick: function(e) {
            $(".dlg-actions").find("button").attr("disabled",true);
            $(".ok-action").html('<i class="fa fa-spinner fa-pulse"></i> Please wait!');
            $(".loader").show();  
			
		    $('.btn_bulk_apply').html('<i class="fa fa-spinner fa-spin" aria-hidden="true" style="font-size: 14px;color: #fff;"></i> processing'); 
			$('.btn_bulk_apply').attr("disabled", true);	
			
				 		
			 $.ajax({
				url : base_url+"attendance/update_ajax_attendance_status",
				method : "POST",
				async: true,
				dataType: 'json',			
				 data: $(".add-ajax-redirect-form").serialize(),			
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
						$('.btn_bulk_apply').html('<i class="fa fa-refresh"></i> Update Attendance');
						$('.btn_bulk_apply').attr("disabled", false);
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
				$('.btn_bulk_apply').html('<i class="fa fa-refresh"></i> Update Attendance');
				$('.btn_bulk_apply').attr("disabled", false);
            });		
			
		 return false;		     
        			
         }
        }
      });
      confirmDlg.show();		 
	 
  });
	
	
});
</script>
