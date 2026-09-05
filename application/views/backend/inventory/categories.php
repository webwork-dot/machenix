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
            <div class="row align-items-center gy-1">
               <div class="col-lg-3 col-md-12">
                  <h5 class="mb-0"><b>Total Categories <span id="total_count"> (<?= count($parent_categories);?>)</span></b></h5>
               </div>
               <div class="col-lg-6 col-md-8 col-12">
                  <div class="d-flex align-items-center">
                     <div class="input-group input-group-merge category-search-group me-1">
                        <span class="input-group-text"><i class="fa fa-search text-muted"></i></span>
                        <input type="text" id="category_search_input" class="form-control" placeholder="Search category by name..." autocomplete="off">
                        <span class="input-group-text cursor-pointer" id="btn_category_clear" style="display: none;" title="Clear search">
                           <i class="fa fa-times text-muted"></i>
                        </span>
                     </div>
                     <button type="button" id="btn_category_search" class="btn btn-primary waves-effect waves-float waves-light text-nowrap me-1">
                        <i class="fa fa-search"></i> Search
                     </button>
                     <button type="button" id="btn_category_reset" class="btn btn-outline-danger waves-effect waves-float waves-light text-nowrap" style="display: none;">
                        <i class="fa fa-undo"></i> Reset
                     </button>
                  </div>
               </div>
               <div class="col-lg-3 col-md-4 col-12 text-md-end text-start">
                  <a href="<?php echo site_url('inventory/category/add'); ?>" class="btn btn-primary waves-effect waves-float waves-light" aria-controls="DataTables_Table_0"><span><i data-feather='plus'></i><?= get_phrase('add_new_category');?></span></a>
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
<script>
   var initialCategoriesHtml = '';
   var initialCountHtml = '';
   var isCategorySearchActive = false;

   $(document).ready(function () {
       // Cache the initial parent categories HTML and count
       initialCategoriesHtml = $('.categories-panel-group').html();
       initialCountHtml = $('#total_count').html();

       // Handle input change to toggle clear button
       $('#category_search_input').on('input keyup', function (e) {
           var val = $(this).val();
           if (val.length > 0) {
               $('#btn_category_clear').show();
               $('.category-search-group').addClass('has-clear');
           } else {
               $('#btn_category_clear').hide();
               $('.category-search-group').removeClass('has-clear');
               if (isCategorySearchActive) {
                   resetCategorySearch();
               }
           }

           // If Enter key pressed, perform search
           if (e.which === 13 || e.keyCode === 13) {
               e.preventDefault();
               performCategorySearch();
           }
       });

       // Search button click
       $('#btn_category_search').on('click', function () {
           performCategorySearch();
       });

       // Clear button click
       $('#btn_category_clear').on('click', function () {
           $('#category_search_input').val('').focus();
           $('#btn_category_clear').hide();
           $('.category-search-group').removeClass('has-clear');
           if (isCategorySearchActive) {
               resetCategorySearch();
           }
       });

       // Reset button click
       $('#btn_category_reset').on('click', function () {
           resetCategorySearch();
       });

       // Trigger reset from empty state click
       $(document).on('click', '.btn_reset_search_trigger', function () {
           resetCategorySearch();
       });
   });

   function performCategorySearch() {
       var keyword = $('#category_search_input').val().trim();
       if (keyword === '') {
           resetCategorySearch();
           return;
       }

       // Show loading indicator
       var origBtnHtml = $('#btn_category_search').html();
       $('#btn_category_search').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Searching...');
       $('.categories-panel-group').css('opacity', '0.5');

       $.ajax({
           url: '<?= base_url(); ?>inventory/search_categories',
           type: 'POST',
           data: { keyword: keyword },
           success: function (response) {
               $('#btn_category_search').prop('disabled', false).html(origBtnHtml);
               $('.categories-panel-group').css('opacity', '1');

               try {
                   var obj = (typeof response === 'object') ? response : JSON.parse(response);
                   if (obj.result == 1) {
                       isCategorySearchActive = true;
                       $('.categories-panel-group').html(obj.html_content);
                       $('#total_count').html(' ' + obj.count_text);
                       $('#btn_category_reset').show();
                       $('#btn_category_clear').show();
                       $('.category-search-group').addClass('has-clear');

                       // Re-initialize feather icons if available
                       if (typeof feather !== 'undefined') {
                           feather.replace({ width: 14, height: 14 });
                       }

                       // Re-initialize Bootstrap tooltips if available
                       if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                           var tooltipTriggerList = [].slice.call(document.querySelectorAll('.categories-panel-group [data-bs-toggle="tooltip"]'));
                           tooltipTriggerList.map(function (tooltipTriggerEl) {
                               return new bootstrap.Tooltip(tooltipTriggerEl);
                           });
                       }
                   }
               } catch (e) {
                   console.error('Error parsing search response', e);
               }
           },
           error: function () {
               $('#btn_category_search').prop('disabled', false).html(origBtnHtml);
               $('.categories-panel-group').css('opacity', '1');
           }
       });
   }

   function resetCategorySearch() {
       $('#category_search_input').val('');
       $('#btn_category_clear').hide();
       $('.category-search-group').removeClass('has-clear');
       $('#btn_category_reset').hide();
       $('.categories-panel-group').html(initialCategoriesHtml).css('opacity', '1');
       $('#total_count').html(initialCountHtml);
       isCategorySearchActive = false;

       // Re-initialize feather icons
       if (typeof feather !== 'undefined') {
           feather.replace({ width: 14, height: 14 });
       }

       // Re-initialize Bootstrap tooltips
       if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
           var tooltipTriggerList = [].slice.call(document.querySelectorAll('.categories-panel-group [data-bs-toggle="tooltip"]'));
           tooltipTriggerList.map(function (tooltipTriggerEl) {
               return new bootstrap.Tooltip(tooltipTriggerEl);
           });
       }
   }
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
   .cursor-pointer {
   cursor: pointer !important;
   }
   .category-search-group .input-group-text:first-child {
   border-right: 0 !important;
   border-top-left-radius: 0.357rem !important;
   border-bottom-left-radius: 0.357rem !important;
   border-color: #d8d6de;
   }
   .category-search-group .form-control {
   border-left: 0 !important;
   border-right: 1px solid #d8d6de !important;
   border-top-right-radius: 0.357rem !important;
   border-bottom-right-radius: 0.357rem !important;
   border-color: #d8d6de;
   }
   .category-search-group.has-clear .form-control {
   border-right: 0 !important;
   border-top-right-radius: 0 !important;
   border-bottom-right-radius: 0 !important;
   }
   .category-search-group #btn_category_clear {
   border-left: 0 !important;
   border-right: 1px solid #d8d6de !important;
   border-top-right-radius: 0.357rem !important;
   border-bottom-right-radius: 0.357rem !important;
   border-color: #d8d6de;
   }
   .category-search-group:focus-within .input-group-text,
   .category-search-group:focus-within .form-control {
   border-color: #7367f0 !important;
   box-shadow: none !important;
   }
   .bg-light-primary {
   background-color: rgba(115, 103, 240, 0.12) !important;
   color: #7367f0 !important;
   }
   .categories-panel-group .badge {
   font-weight: 500;
   padding: 0.35em 0.65em;
   border-radius: 4px;
   }
</style>