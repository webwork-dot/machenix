<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />


<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/datatables.buttons.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/jszip.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/pdfmake.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/vfs_fonts.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/buttons.html5.min.js"></script>
<style>
.add-btn {
    margin-top: 2.4rem;
}
</style>


<div class="filter-accordion accordion mx-filter" id="accordionFilter">
   <div class="collapse-margin card ">
      <div class="card-header" id="headingOne" data-toggle="collapse" role="button" data-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
         <span class="lead collapse-title">
            <h4 class="mb-0"><i class="feather icon-filter"></i> Filter <span id="total_count">(0)</span></h4>
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
                                     <div class="form-group mb-0">   
                                      <label>Date</label>
                                      <div class="form-group">
                                        <input type="text" class="form-control datepicker_rg" name="date_range" value="<?php if(isset($_GET['date_range'])) { echo $_GET['date_range']; }?>" placeholder="Search  Date" autocomplete="off" readonly>
                                     </div>
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
                  <h5 class="mb-0"><b>Total Holiday Data</h5>
               </div>
            </div>
         </div>
         <div class="card-datatable d-report mb-2"> 
		 <a href="<?php echo site_url('hr/holiday/add'); ?>" class="dt-button add-new  add-btn btn btn-primary" tabindex="0" aria-controls="DataTables_Table_0" ><span><i data-feather='plus'></i> Add Holiday</span></a>  
          <div class="card-body pt-0"> 
            <table class="table leads-table" id="repots-datatable">
               <thead>
                  <tr>                                    
                    <th>Sr No</th>
					<th>Staff Category</th>
					<th>Holiday Nme</th>
					<th>Holiday Date</th>
					<th>Added Date</th>
					<th>Actions</th>
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
                "url": "<?php echo base_url('hr/get_holidays'); ?>",
                "dataType": "json",
                "type": "POST",
                "data": function(data){
				    data.date_range = "<?php if(isset($_GET['date_range'])) { echo $_GET['date_range']; }?>";	
                    data.keywords = "<?php if(isset($_GET['keywords'])) { echo $_GET['keywords']; }?>";			
                }
                
            },     
            "columns": [
                { "data": "sr_no" },
                { "data": "salary_type" },
                { "data": "holiday_name" },
                { "data": "holiday_date" },
                { "data": "date" },
                { "data": "action" },
            ], 
            
           "buttons": [
                {
                    "extend": 'excel',
                    "text": '<button class="btn btn-success waves-effect waves-float waves-light"><i class="fa fa-file-excel-o"></i>  Excel</button>',
                    "exportOptions": {
                       "columns": [0,1,2,3,4,5]
                    }
                },
                {
                    "extend": 'pdfHtml5',
                    "orientation": 'landscape',
                    "text": '<button class="btn btn-danger waves-effect waves-float waves-light"><i class="fa fa-file-pdf-o"></i> PDF</button>',  
                    "exportOptions": {
                       "columns": [0,1,2,3,4,5]
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

<script>
$(document).ready(function () {
    //Date range picker
    $('.datepicker_rg').daterangepicker({
      autoUpdateInput: false,
     autoApply: true,
        locale: {
         format: 'DD-MM-YYYY', 
         cancelLabel: 'Clear'
     },  
    })
    //Date range picker with time picker

  $('.datepicker_rg').on('apply.daterangepicker', function(ev, picker) {
      $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));
  });

  })
</script>