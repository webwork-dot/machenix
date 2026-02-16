<style>
.pop-report.dataTable>tbody>tr>td {
    padding: 7px 10px;
    font-weight: 500;
}
.pop-modal .dataTables_scrollHeadInner{ width:auto!important}
</style>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/fixedcolumns/4.1.0/css/fixedColumns.dataTables.min.css">
<script src="https://cdn.datatables.net/fixedcolumns/4.1.0/js/dataTables.fixedColumns.min.js"></script>


<?php

	$emp=$this->common_model->getRowById('candidate','emp_id,name,salary_type,paid_leaves',array('emp_id'=>$param2));
	$used_pl=$this->common_model->getUsedPaidLeave($param2,date('Y'));
	$single_loans_details=$this->common_model->getResultById('paid_leave_history','paid_leave,month_name,year,added_by_name',array('emp_id'=>$param2));

//echo $this->db->last_query();exit();
?> 			
				
				<?php if(empty($single_loans_details)):?>	
                    <div class="row mt-3 mb-3 d-flex justify-content-center">						
					    <div class="col-12 col-sm-12 mb-1">
						<h5 class="text-center">No Paid Leave History!</h5>
						</div>
					</div> 			
				<?php else:?>					
                    <div class="row mb-10">						
					     <div class="col-12 col-sm-3 mb-1">
						  <div class="form-group">
							 <label class="form-label">Staff Name</label>
							 <p><?= $emp['name'];?></p>
						  </div>
						</div>  

						<div class="col-12 col-sm-3 mb-1">
						  <div class="form-group">
							 <label class="form-label">Salary Type</label>
							 <p><?= get_phrase($emp['salary_type']);?></p>
						  </div>
						</div>	
						
						<div class="col-12 col-sm-3 mb-1">
						  <div class="form-group">
							 <label class="form-label">Paid Leaves</label>
							 <p><?= $emp['paid_leaves'];?></p>
						  </div>
						</div>	
						
						<div class="col-12 col-sm-3 mb-1">
						  <div class="form-group">
							 <label class="form-label">Remaining Paid Leave</label>
							 <p><?= $emp['paid_leaves']-$used_pl;?></p>
						  </div>
						</div>	
                      </div>     
                 
				 
				 
        <div class="card-datatable mb-2 pop-modal">
          <table class="table table-bordered pop-report zero-configuration no-padd">
          <thead>
            <tr>
                <th>#</th>
                <th>Paid Leave</th>
                <th>PL Month</th>
                <th>Updated By</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($single_loans_details as $key => $xm): ?>
            <tr>
              <td><?php echo $key+1;?></td> 
              <td><?php echo $xm['paid_leave'];?></td>
              <td><?php echo $xm['month_name'].'-'. $xm['year'];?></td>
              <td><?php echo $xm['added_by_name'];?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
		
      </div>


<script>
$(document).ready(function($) {
    // Function to initialize DataTable
    function initializeDataTable() {
        $('.zero-configuration').DataTable({
            "dom": '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l B><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            "scrollY": 250,
            "scrollX": true,
            "ordering": false,
            "pageLength": 50,
            "language": {
                sLengthMenu: "_MENU_"
            },
            "initComplete": function(settings, json) {
                $(".loader").hide();
            },
            "buttons": [
                {
                    "extend": 'csv',
                    "text": '<button class="btn btn-success waves-effect waves-float waves-light"><i class="fa fa-file-excel-o"></i>  Excel</button>',
                },
                {
                    "extend": 'pdfHtml5',
                    "orientation": 'landscape',
                    "text": '<button class="btn btn-danger waves-effect waves-float waves-light"><i class="fa fa-file-pdf-o"></i> PDF</button>',
                }
            ],
        });
    }

    // Initialize DataTable when the modal is shown
    $('#scrollable-modal').on('shown.bs.modal', function() {
        // Destroy the existing DataTable instance if it exists
        if ($.fn.DataTable.isDataTable('.zero-configuration')) {
            $('.zero-configuration').DataTable().destroy();
        }
        // Show loader before initializing DataTable
        $(".loader").show();
        // Initialize DataTable
        initializeDataTable();
    });
});

</script>
<?php endif;?>		