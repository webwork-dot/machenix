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
// $param2 = lesson id and $param3 = type  
if(isset($param3) && $param3!=''){	
	$gloan=$this->common_model->getRowById('loans','emp_id,emp_name,loan_type,instalment,amount,emi,amount_paid,applied_date',array('emp_id'=>$param2,'loan_type'=>$param3,'status'=>'ongoing'));
	
	$single_loans_details=$this->common_model->getResultById('loan_repayments','amount,repayment_date,month,year',array('emp_id'=>$param2,'loan_type'=>$param3));
}
else{
	$gloan=$this->common_model->getRowById('loans','emp_id,emp_name,loan_type,instalment,amount,emi,amount_paid,applied_date',array('id'=>$param2));
	$emp_id=$gloan['emp_id'];
	$single_loans_details=$this->common_model->getResultById('loan_repayments','amount,repayment_date,month,year',array('loan_id'=>$param2,'emp_id'=>$emp_id));
}
//echo $this->db->last_query();exit();
?> 			
				
				<?php if(empty($gloan)):?>	
                    <div class="row mt-3 mb-3 d-flex justify-content-center">						
					    <div class="col-12 col-sm-3 mb-1">
						<h5>No Active Loans!</h5>
						</div>
					</div> 			
				<?php else:?>					
                    <div class="row mb-10">						
					     <div class="col-12 col-sm-3 mb-1">
						  <div class="form-group">
							 <label class="form-label">Staff Name</label>
							 <p><?= $gloan['emp_name'];?></p>
						  </div>
						</div>  

						<div class="col-12 col-sm-3 mb-1">
						  <div class="form-group">
							 <label class="form-label">Loan Type</label>
							 <p><?= get_phrase($gloan['loan_type']);?></p>
						  </div>
						</div>	
						
						<div class="col-12 col-sm-3 mb-1">
						  <div class="form-group">
							 <label class="form-label">Loan Amount</label>
							 <p><?= indian_price($gloan['amount']);?></p>
						  </div>
						</div>	
						
						<div class="col-12 col-sm-3 mb-1">
						  <div class="form-group">
							 <label class="form-label">Instalment</label>
							 <p><?= $gloan['instalment'];?></p>
						  </div>
						</div>	
						
					

						<div class="col-12 col-sm-3 mb-1">
						  <div class="form-group">
							 <label class="form-label">EMI</label>
							 <p><?= indian_price($gloan['emi']);?></p>
						  </div>
						</div>	
					
						<div class="col-12 col-sm-3 mb-1">
						  <div class="form-group">
							 <label class="form-label">Amount Paid</label>
							 <p><?= indian_price($gloan['amount_paid']) ?? 0;?></p>
						  </div>
						</div>	

						<div class="col-12 col-sm-3 mb-1">
						  <div class="form-group">
							 <label class="form-label">Balance Loan</label>
							 <p><?= indian_price($gloan['amount']-$gloan['amount_paid']);?></p>
						  </div>
						</div>	
						
						<div class="col-12 col-sm-3 mb-1">
						  <div class="form-group">
							 <label class="form-label">Applied On</label>
							 <p><?= date("d M, Y", strtotime($gloan['applied_date']));?></p>
						  </div>
						</div>	
						
          					
                      </div>     
                 
				 
				 
        <div class="card-datatable mb-2 pop-modal">
          <table class="table pop-report zero-configuration no-padd">
          <thead>
            <tr>
                <th>#</th>
                <th>Amount</th>
                <th>Repayment Date</th>
                <th>Salary Month</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($single_loans_details as $key => $xm): ?>
            <tr>
              <td><?php echo $key+1;?></td> 
              <td><?php echo $xm['amount'];?></td>
              <td><?php echo date("d M, Y", strtotime($xm['repayment_date']));?></td>
              <td><?php echo $xm['month'].'-'. $xm['year'];?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
		
      </div>


<script>
    $(document).ready(function($) { 
        $('.zero-configuration').DataTable({ 
            "scrollY": 150,
            "scrollX": true,
            "ordering": false,
            "pageLength": 50,
          "dom": '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
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
    });
</script>
<?php endif;?>		