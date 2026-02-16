<?php
defined('BASEPATH') OR exit('No direct script access allowed');
if ( ! function_exists('pagintaion'))
{
    function pagintaion($total_rows, $per_page_item){
        $config['per_page']        = $per_page_item;
        $config['num_links']       = 2;
        $config['total_rows']      = $total_rows;
        $config['full_tag_open']   = '<ul class="pagination justify-content-center">';
        $config['full_tag_close']  = '</ul>';
        $config['prev_link']       = '<i class="fas fa-chevron-left"></i>';
        $config['prev_tag_open']   = '<li class="page-item">';
        $config['prev_tag_close']  = '</li>';
        $config['next_link']       = '<i class="fas fa-chevron-right"></i>';
        $config['next_tag_open']   = '<li class="page-item">';
        $config['next_tag_close']  = '</li>';
        $config['cur_tag_open']    = '<li class="page-item active disabled"> <span class="page-link">';
        $config['cur_tag_close']   = '</span></li>';
        $config['num_tag_open']    = '<li class="page-item">';
        $config['num_tag_close']   = '</li>';
        $config['first_tag_open']  = '<li class="page-item">';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open']   = '<li class="page-item">';
        $config['last_tag_close']  = '</li>';
        return $config;
    }
}

if ( ! function_exists('paginate')){
    function paginate($url, $total_rows)
	{
		//initialize pagination
		$page = $this->security->xss_clean($this->input->get('page'));
		$per_page = $this->input->get('show', true);
		if (empty($page)) {
			$page = 0;
		}

		if ($page != 0) {
			$page = $page - 1;
		}

		if (empty($per_page)) {
			$per_page = 20;
		}
		$config['num_links'] = 4;
		$config['base_url'] = $url;
		$config['total_rows'] = $total_rows;
		$config['per_page'] = $per_page;
		$config['reuse_query_string'] = true;
		$this->pagination->initialize($config);

		return array('per_page' => $per_page, 'offset' => $page * $per_page);
	}
	
}
	
