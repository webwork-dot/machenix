<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/category.css">
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>../app-assets/vendors/css/tables/datatable/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="<?php echo base_url(); ?>../app-assets/vendors/js/tables/datatable/datatables.buttons.min.js"></script>
<script src="<?php echo base_url(); ?>../app-assets/vendors/js/tables/datatable/jszip.min.js"></script>
<script src="<?php echo base_url(); ?>../app-assets/vendors/js/tables/datatable/pdfmake.min.js"></script>
<script src="<?php echo base_url(); ?>../app-assets/vendors/js/tables/datatable/vfs_fonts.js"></script>
<script src="<?php echo base_url(); ?>../app-assets/vendors/js/tables/datatable/buttons.html5.min.js"></script>
<script src="<?php echo base_url(); ?>../app-assets/vendors/js/tables/datatable/buttons.print.min.js"></script>

<!-- Bordered table start -->
 <?php include('nav/nav_settings.php'); ?>
<div class="row" id="table-bordered">
   <div class="col-12">
      <div class="card">
         <div class="card-body">
            <div class="row">
               <div class="col-md-6 mt-10">
                  <h5 class="mb-0"><b>Total Categories <span id="total_count"> (<?= count($parent_categories);?>)</span></b></h5>
               </div>
               <div class="col-md-6">
                  <a href="<?php echo site_url('inventory/category/add'); ?>" class="pull-right btn mt-0 btn-primary waves-effect waves-float waves-light" aria-controls="DataTables_Table_0" ><span><i data-feather='plus'></i><?= get_phrase('add_new_category');?></span></a>
               </div>
            </div>
         </div>
         <div class="card-datatable mb-0 col-md-12">
            <div class="card-body pt-0">
               <div class="row">
                  <div class="categories-panel-group nested-sortable">
                     <?php if (!empty($parent_categories)):
                        // json_encode($parent_categories);exit();
                         $has_subcategory = true;
                         foreach ($parent_categories as $parent_category): ?>
                     <div class="panel-group" draggable="false">
                        <div data-item-id="<?= $parent_category->id; ?>" class="panel panel-default">
                           <div id="panel_heading_parent_<?= $parent_category->id; ?>" class="panel-heading <?= !empty($parent_category->has_subcategory) ? 'panel-heading-parent' : ''; ?>" data-item-id="<?= $parent_category->id; ?>" href="#collapse_<?= $parent_category->id; ?>">
                              <div class="left">
                                 <?php if (!empty($parent_category->has_subcategory)): ?>
                                 <i class="fa fa-plus"></i>
                                 <?php else: ?>
                                 <i class="fa fa-circle" style="font-size: 8px;"></i>
                                 <?php endif; ?>
                                 <?= category_name($parent_category); ?> 
                              </div>
                              <div class="right">
                                 <div class="btn-groups">
                                    <a href="<?= base_url(); ?>inventory/category/edit/<?= $parent_category->id; ?>" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit"><button type="button" class="btn  icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
                                    <a href="#" onclick="showDeleteConfirmation('<?= base_url(); ?>inventory/category/delete/<?= $parent_category->id; ?>')"
                                       data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete">
                                        <button type="button" class="btn icon-btn-del">
                                            <i class="fa fa-trash" aria-hidden="true"></i>
                                        </button>
                                    </a>
                                 </div>
                              </div>
                           </div>
                           <?php if (!empty($parent_category->has_subcategory)): ?>
                           <div id="collapse_<?= $parent_category->id; ?>" class="panel-collapse collapse" aria-expanded="true" style="">
                              <div class="panel-body" style="padding: 20px 0;">
                                 <div class="spinner">
                                    <div class="bounce1"></div>
                                    <div class="bounce2"></div>
                                    <div class="bounce3"></div>
                                 </div>
                              </div>
                           </div>
                           <?php endif; ?>
                        </div>
                     </div>
                     <?php endforeach;
                        endif; ?>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<script>
    function showDeleteConfirmation(url) {
        // Using a standard JavaScript alert for confirmation
        var isConfirmed = confirm('Are you sure want to delete!');
        if (isConfirmed) {
            // If user clicks OK, proceed with the delete action
            window.location.href = url;
        } else {
            // If user clicks Cancel, do nothing or provide feedback
        }
    }
</script>
<script>
   $(document).on("click", ".panel .panel-heading", function (e) {
       if ($(e.target).is('div') || $(e.target).is('span') || $(e.target).is('.fa-plus') || $(e.target).is('.fa-minus')) {
           var id = $(this).attr('data-item-id');
           $('#collapse_' + id).collapse("toggle");
           $('.left .fa', this).toggleClass('fa-plus').toggleClass('fa-minus');
       }
   });
   
</script>
<script>
   $(document).on('click', '.panel-heading-parent', function (e) {
       var id = $(this).attr('data-item-id');
       if ($(e.target).hasClass('btn')) {
           return true;
       }
       if ($('#panel_heading_parent_' + id).hasClass('parent-panel-open')) {
           $('#collapse_' + id).removeClass("show");
           $('#panel_heading_parent_' + id).removeClass("parent-panel-open");
           return false;
       }else{
           $('#collapse_' + id).addClass("show");
           $('#panel_heading_parent_' + id).addClass("parent-panel-open");
       }
       $('#collapse_' + id + ' .spinner').css('visibility', 'visible');
       var data = {
           'id': id,
           'lang_id': 0
       };
       $.ajax({
           url: base_url + 'inventory/load_categories',
           type: 'POST',
           data: data,
           success: function (response) {
               var obj = JSON.parse(response);
               if (obj.result == 1) {
                   setTimeout(function () {
                       $('#panel_heading_parent_' + id).addClass('parent-panel-open');
                       document.getElementById('collapse_' + id).innerHTML = obj.html_content;
                   }, 300);
               }
           }
       });
   });
</script>
<style>
   .btn-group-option {
   display: inline-block !important;
   }
   .spinner {
   visibility: hidden;
   }
   .spinner > div {
   width: 16px;
   height: 16px;
   background-color: #999;
   }
   .cursor-default {
   cursor: default !important;
   }
</style>