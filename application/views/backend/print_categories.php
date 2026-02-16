<?php defined('BASEPATH') or exit('No direct script access allowed');
function print_sub_categories($parent_id)
{
    $ci =& get_instance();
    $ci->load->model('category_model');  
    $subcategories = $ci->category_model->get_subcategories_by_parent_id($parent_id);
    if (!empty($subcategories)) {
        foreach ($subcategories as $category) {
             $delete_url=base_url().'inventory/category/delete/'.$category->id;
             $url=base_url().'inventory/category/edit/'.$category->id;
             $action='';
             $action='<a href="'.$url.'" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Edit"><button type="button" class="btn icon-btn-edit"><i class="fa fa-pencil" aria-hidden="true"></i></button></a>
             
                 <a href="#" onclick="showDeleteConfirmation(\''.$delete_url.'\')"
                   data-bs-toggle="tooltip" data-bs-placement="bottom" title="Delete">
                  <button type="button" class="btn icon-btn-del">
                    <i class="fa fa-trash" aria-hidden="true"></i>
                  </button>
                </a>';
            
            $i = 0;
            if ($i == 0) {
                if (!empty($category->has_subcategory)) {
                    echo '<div class="panel-group">';
                } else {
                    echo '<div class="panel-group cursor-default">';
                }
                echo '<div class="panel panel-default">';
                if (!empty($category->has_subcategory)) {
                    $div_content = '<div class="panel-heading" data-item-id="' . $category->id . '" href="#collapse_' . $category->id . '">';
                } else {
                    $div_content = '<div class="panel-heading">';
                }
                $div_content .= '<div class="left">';
                if (!empty($category->has_subcategory)) {
                    $div_content .= '<i class="fa fa-plus"></i>';
                } else {
                    $div_content .= '<i class="fa fa-circle" style="font-size: 8px;"></i>';
                }
                $div_content .= category_name($category);
                $div_content .= '</div>';
                $div_content .= '<div class="right">';
                $div_content .= '<div class="btn-groups btn-group-option">';
                $div_content .=$action;
                $div_content .= '</div>';
                $div_content .= '</div>';
                $div_content .= '</div>';
                echo $div_content;
                echo '<div id="collapse_' . $category->id . '" class="panel-collapse collapse"><div class="panel-body nested-sortable" data-parent-id="' . $category->id . '">';
            } else {
                echo '<div id="collapse_' . $category->id . '" class="list-group-item" data-item-id="' . $category->id . '">' . category_name($category) . '<span class="id">(' . trans("id") . ': ' . $category->id . ')</span>' . '</div>';
            }
            print_sub_categories($category->id);
            $i++;
            if ($i > 0) {
                echo '</div>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
            }
        }
    }
} ?>

<div class="panel-body">
    <?= print_sub_categories($parent_category_id); ?>
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
