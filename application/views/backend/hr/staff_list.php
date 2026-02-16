<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/datatables.buttons.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/jszip.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/pdfmake.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/vfs_fonts.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/buttons.html5.min.js"></script>


<div class="row" id="table-bordered">
   <div class="col-12">
      <div class="card">
       
         <?php include '_filter_staff.php';?>
         
         <div class="card-body">
            <div class="row">
               <div class="col-md-12 mt-10">
                  <h5 class="mb-0"><b>Total Staff <span id="total_count"> (0)</span></b></h5>
               </div>
            </div>
         </div>
         <div class="card-datatable d-report mb-2">
          <div class="card-body">           
            <table class="table leads-table tfixed" id="flash-datatable">
               <thead>
                  <tr>
                                    
					<th style="width:45px">Sr No</th>
                    <th style="width:130px">Staff Name</th>
                    <th style="width:60px">Emp Id</th>
                    <th style="width:130px">Staff Type</th>
                    <th style="width:100px">State</th>
                    <th style="width:80px">City</th>
                    <th style="width:70px">Paid Leaves</th>
                    <th style="width:70px">Balance PL</th>
                    <th style="width:100px">Kyc Date</th>
                    <th style="width:80px">Status</th>
                    <th style="width:140px">Action</th>
                  </tr>
               </thead>
            </table>
         </div>
         </div>
      </div>
     </div>
</div>

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
            

<script type="text/javascript">
    $(document).ready(function($) { 
        var dataTable = $('#flash-datatable').DataTable({ 
            "dom": '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l B><"col-sm-12 col-md-6">>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
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
                "url": "<?php echo base_url('hr/get_staff_list'); ?>",
                "dataType": "json",
                "type": "POST",
                "data": function(data){
                    data.date_range = "<?php if(isset($_GET['date_range'])) { echo $_GET['date_range']; }?>";	               
                    data.keywords = "<?php if(isset($_GET['keywords'])) { echo $_GET['keywords']; }?>";			
                   data.salary_type = "<?php if(isset($_GET['salary_type'])) { echo $_GET['salary_type']; }?>";				
                }
            },   
                    
            "columns": [
				{ "data": "sr_no" },
                { "data": "name" },   
                { "data": "emp_id" },   
                { "data": "salary_type" },   
                { "data": "state" },  
                { "data": "city" },  
                { "data": "paid_leaves" },  
                { "data": "balance_leaves" },  
                { "data": "date" },  
                { "data": "status" },  
                { "data": "action" },
            ], 
           
            "buttons": [
                {
                    "extend": 'excel',
                    "text": '<button class="btn btn-success waves-effect waves-float waves-light"><i class="fa fa-file-excel-o"></i>  Excel</button>',
                    "exportOptions": {
                         "columns": [0,1,2,3,4,5,6,7,8,9]
                    }
                },
                {
                    "extend": 'pdfHtml5',
                    "orientation": 'landscape',
                    "text": '<button class="btn btn-danger waves-effect waves-float waves-light"><i class="fa fa-file-pdf-o"></i> PDF</button>',  
                    "exportOptions": {
                        "columns": [0,1,2,3,4,5,6,7,8,9]
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
                {
                    "targets": 8, // your case first column
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
</script>
