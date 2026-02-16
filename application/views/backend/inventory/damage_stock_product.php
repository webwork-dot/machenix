<link rel="stylesheet" type="text/css" href="<?= base_url();?>app-assets/vendors/css/tables/datatable/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/datatables.buttons.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/jszip.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/pdfmake.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/vfs_fonts.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/buttons.html5.min.js"></script>
<script src="<?= base_url();?>app-assets/vendors/js/tables/datatable/buttons.print.min.js"></script>


<div class="row" id="table-bordered">
    <?php include('nav/nav_dmg_stock.php'); ?>
    
   
   <div class="col-12">
      <div class="card">
          
        <?php $warehouse_id = (empty($this->input->get('warehouse', true))) ? $warehouse_list[0]['id'] : $this->input->get('warehouse', true); ?>
        <div class="col-md-12 p-1">
    		<div class="fixedElement" id="fixedElement">
    			<ul class="nav nav-pills bg-nav-pills nav-justified mb-0" style="border: 1px solid black;">
    				<?php
                    $active='active';
                    foreach($warehouse_list as $warehouse){?>
                    <li class="nav-item">
                        <a href="<?php echo base_url();?>inventory/damage-stock-product?warehouse=<?php echo $warehouse['id'];?>" class="nav-link h-100 <?php echo ($warehouse_id == $warehouse['id']) ? 'active' : ''; ?>" style="border-radius: 0px;">
                            <i class="mdi mdi-home-variant d-md-none d-block"></i>
                            <span class="d-none d-md-block"><?php echo $warehouse['name'];?></span>
                        </a>
                    </li>
                    <?php $active='';}?>	
    			</ul>
    		</div>
	    </div>
          
         <div class="card-body">
            <div class="row">
               <div class="col-md-12 mt-10">
                  <h5 class="mb-0"><b>Total Damage Stock <span id="total_count"> (0)</span></b></h5>
               </div>
            </div>
         </div>
        <div class="card-datatable d-report mb-2">
            <!--<a href="javascript:void(0);" onclick="moveToScrap();" class="dt-button add-new desktop-tab add-btn btn btn-outline-primary waves-effect" tabindex="0" aria-controls="DataTables_Table_0">-->
            <!--    <span><i class="feather icon-repeat"></i> Move To Scrap (<span id="selection">0</span>) </span>-->
            <!--</a>-->
                  
          <table class="table leads-table" id="report-datatable">
               <thead>
                  <tr>
					<!--<th>Sr. no</th>-->
					<th>#</th>
					<th>Product</th>
					<th>SKU</th>
					<th>Qty</th>
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
                "url": "<?php echo base_url('inventory/get_damage_stock_product_history'); ?>",
                "dataType": "json",
                "type": "POST",
                "data": function(data){
                       var date_range="";		
                       data.warehouse = '<?php echo $warehouse_id; ?>';
                },
                "beforeSend": function() {
                    $('.loader').show();
                },
                "complete": function() {
                    $('.loader').hide();
                }
            },   
                     
            "columns": [
                // { "data": "sr_no" },
                { "data": "action" },
                { "data": "product_name" },
                { "data": "sku" },
                { "data": "product_qty" },
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
    
    function counterScrap() {
        let counter = 0;
        let elements = document.querySelectorAll('.scrapbox');
        
        elements.forEach((ele) => {
            if(ele.checked == true) {
                counter++;
            }
        });
        
        document.querySelector('#selection').innerHTML = counter;
    }
    
    function moveToScrap() {
        let counter = document.querySelector('#selection');
        if(counter.innerHTML == '0') {
            Swal.fire({
    			title: "Warning!",
    			text: "Please Select Atleast 1 Product",
    			icon: "info",
    			customClass: {
    				confirmButton: "btn btn-primary"
    			},
    			buttonsStyling: !1
    		})
        } else {
            
            Swal.fire({
              title: "Are you sure?",
              text: "You want to move selected products to scrap!",
              icon: "warning",
              showCancelButton: true,
              confirmButtonColor: "#3085d6",
              cancelButtonColor: "#d33",
              confirmButtonText: "Yes"
            }).then((result) => {
              if (result.isConfirmed) {

                let data = [];
                let elements = document.querySelectorAll('.scrapbox');
                elements.forEach((ele) => {
                    if(ele.checked == true) {
                        data.push(ele.getAttribute('data-sku'))
                    }
                });
                
                $.ajax({
                  type: 'POST',
                  dataType: 'json',
                  data: {sku: data, warehouse: '<?php echo $warehouse_id; ?>'},
                  url: '<?php echo base_url(); ?>inventory/move_to_scrap',
                  success: function(res) {
                      console.log(res)
                      if(res.status) {
                            Swal.fire({
                    			title: "Success!",
                    			text: "Product Moved to Scrap Successfully!",
                    			icon: "success",
                    			customClass: {
                    				confirmButton: "btn btn-primary"
                    			},
                    			buttonsStyling: !1
                    		}).then(() => {
                    		    window.location.reload();
                    		});
                      } else {
                          Swal.fire({
                    			title: "Error!",
                    			text: "Some Error Occured",
                    			icon: "error",
                    			customClass: {
                    				confirmButton: "btn btn-primary"
                    			},
                    			buttonsStyling: !1
                    		});
                      }
                  }
                });

              }
            });


            
        }
    }
    
</script>


