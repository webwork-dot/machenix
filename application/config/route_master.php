<?php
defined('BASEPATH') OR exit('No direct script access allowed');


/*Production Head*/
$route[$r_production_head . '/products'] = 'production_head/products';
$route[$r_production_head . '/products/add']    = 'production_head/products_form/add';
$route[$r_production_head . '/products/edit/(:num)'] = 'production_head/products_form/edit/$1';

