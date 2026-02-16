<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['num_links'] = 2;
$config['use_page_numbers'] = TRUE;
$config['page_query_string'] = TRUE;
$config['query_string_segment'] = 'page';
$config['first_link'] = '&laquo';
$config['last_link'] = '&raquo';
$config['attributes'] = array('class' => 'page-link');

$config['full_tag_open'] = "<ul class='pagination m-paginate justify-content-center mt-2'>";
$config['full_tag_close'] = "</ul>";
$config['num_tag_open'] = '<li class="page-item">';

$config['num_tag_close'] = '</li>';
$config['cur_tag_open'] = "<li class='disabled'><li class='active page-item'><a  class='page-link' href='#'>";
$config['cur_tag_close'] = "<span class='sr-only'></span></a></li>";

$config['next_link'] = 'Next';
$config['next_tag_open'] = "<li class='page-item next'>";
$config['next_tagl_close'] = "</li>";

$config['prev_link'] = 'Prev';
$config['prev_tag_open'] = "<li class='page-item prev'>";
$config['prev_tagl_close'] = "</li>";

$config['first_tag_open'] = "<li class='page-item page-first'>";
$config['first_tagl_close'] = "</li>";
$config['last_tag_open'] = "<li class='page-item page-last'>";
$config['last_tagl_close'] = "</li>";