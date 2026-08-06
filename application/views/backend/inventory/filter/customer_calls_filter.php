<div class="filter-accordion accordion mx-filter mb-1" id="accordionFilter">
   <div class="collapse-margin card">
      <div class="card-header" id="headingOne" data-toggle="collapse" role="button" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
         <span class="lead collapse-title">
            <h4 class="mb-0"><i class="feather icon-filter"></i> Filter</h4>
         </span>
      </div>
      <div id="collapseOne" class="pb-1 collapse show" aria-labelledby="headingOne" data-parent="#accordionFilter">
         <section class="filter-section">
            <div class="row match-height">
               <div class="col-12">
                  <div class="card mb-0">
                     <div class="card-content">
                        <div class="card-body">
                           <form class="form form-vertical" id="form_filter" method="GET" onsubmit="return false;">
                              <div class="form-body">
                                 <div class="row">
                                    <div class="col-md-3 col-12">
                                       <div class="form-group mb-0">   
                                          <label>Date</label>
                                          <input type="text" autocomplete="off" class="form-control bg-white flatpickr-max" name="date" id="filter_date" value="<?php echo isset($_GET['date']) && !empty($_GET['date']) ? $_GET['date'] : date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" placeholder="YYYY-MM-DD">
                                       </div>
                                    </div>
                                    <div class="col-md-3 col-12">
                                       <label style="display: block;">&nbsp; </label>
                                       <div class="form-group mb-0">
                                          <button type="submit" id="btn_search_filter" class="btn btn-primary mr-1 mb-0 waves-effect waves-float waves-light">Submit</button>
                                          <a href="<?php echo site_url('inventory/customer_calls'); ?>"><button type="button" class="btn btn-outline-danger mr-1">Reset</button></a>
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

<script type="text/javascript">
    $(document).ready(function() {
        if (typeof $.fn.flatpickr !== 'undefined') {
            $('#filter_date').flatpickr({
                dateFormat: "Y-m-d",
                maxDate: "<?php echo date('Y-m-d'); ?>",
                defaultDate: "<?php echo isset($_GET['date']) && !empty($_GET['date']) ? $_GET['date'] : date('Y-m-d'); ?>"
            });
        }

        $('#form_filter').on('submit', function(e) {
            e.preventDefault();
            if (typeof dataTable !== 'undefined') {
                dataTable.draw();
            }
        });
    });
</script>
