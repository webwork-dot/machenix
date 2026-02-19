<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'login';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$r_admin      		='admin';
$r_inventory      	='inventory';
$qc_inventory      	='quality-control';
$r_production_head  ='production_head';
$r_common     		='common';
$r_hr ='hr'; 

include_once "route_master.php";
include_once "route_hrm.php";

$route[$r_inventory . '/system-password/(:num)'] = 'inventory/system_password/$1';

$route[$r_admin . '/staff'] = 'admin/staff';
$route[$r_admin . '/staff/add']    = 'admin/staff_form/staff_add';
$route[$r_admin . '/staff/edit/(:num)'] = 'admin/staff_form/staff_edit/$1';
$route[$r_admin . '/staff/change-password/(:num)'] = 'admin/staff_form/staff_change_password/$1';


$route[$r_inventory . '/manage-access'] = 'inventory/manage_access';
$route[$r_inventory . '/access/add']    = 'inventory/access_form/add';
$route[$r_inventory . '/access/edit/(:num)'] = 'inventory/access_form/edit/$1';

$route[$r_inventory . '/manage-staff'] = 'inventory/manage_staff';
$route[$r_inventory . '/staff/add']    = 'inventory/staff_form/staff_add';
$route[$r_inventory . '/staff/edit/(:num)'] = 'inventory/staff_form/staff_edit/$1';


/* Inventory */
$route[$r_inventory . '/warehouse'] = 'inventory/warehouse';
$route[$r_inventory . '/warehouse/add']    = 'inventory/warehouse_form/warehouse_add';
$route[$r_inventory . '/warehouse/edit/(:num)'] = 'inventory/warehouse_form/warehouse_edit/$1';

$route[$r_inventory . '/supplier'] = 'inventory/supplier';
$route[$r_inventory . '/supplier/add']    = 'inventory/supplier_form/supplier_add';
$route[$r_inventory . '/supplier/edit/(:num)'] = 'inventory/supplier_form/supplier_edit/$1';
$route[$r_inventory . '/supplier/ledger/(:num)'] = 'inventory/supplier_form/supplier_ledger/$1';

$route[$r_inventory . '/company'] = 'inventory/company';
$route[$r_inventory . '/company/add']    = 'inventory/company_form/company_add';
$route[$r_inventory . '/company/edit/(:num)'] = 'inventory/company_form/company_edit/$1';

$route[$r_inventory . '/my-company'] = 'inventory/my_company';
$route[$r_inventory . '/my-company/add']    = 'inventory/my_company_form/my_company_add';
$route[$r_inventory . '/my-company/edit/(:num)'] = 'inventory/my_company_form/my_company_edit/$1';

$route[$r_inventory . '/expense-type'] = 'inventory/expense_type';
$route[$r_inventory . '/expense-type/add']    = 'inventory/expense_type_form/expense_type_add';
$route[$r_inventory . '/expense-type/edit/(:num)'] = 'inventory/expense_type_form/expense_type_edit/$1';

$route[$r_inventory . '/bank-accounts'] = 'inventory/bank_accounts';
$route[$r_inventory . '/bank-accounts/add']    = 'inventory/bank_accounts_form/bank_accounts_add';
$route[$r_inventory . '/bank-accounts/edit/(:num)'] = 'inventory/bank_accounts_form/bank_accounts_edit/$1';

$route[$r_inventory . '/customer'] = 'inventory/customer';
$route[$r_inventory . '/customer/add']    = 'inventory/customer_form/customer_add';
$route[$r_inventory . '/customer/edit/(:num)'] = 'inventory/customer_form/customer_edit/$1';

$route[$r_inventory . '/leads/add']               = 'inventory/leads_form/leads_add';
$route[$r_inventory . '/leads/edit/(:num)']       = 'inventory/leads_form/leads_edit/$1';
$route[$r_inventory . '/leads/move/(:num)']       = 'inventory/leads_form/leads_move/$1';
$route[$r_inventory . '/leads/(:any)']            = 'inventory/leads/$1';
// $route[$r_inventory . '/leads/(:any)']            = 'inventory/leads_form/leads/$1';

$route[$r_inventory . '/category']                      	= 'inventory/categories';
$route[$r_inventory . '/category/add']                  	= 'inventory/category_form/add';
$route[$r_inventory . '/category/edit/(:num)']          	= 'inventory/category_form/edit/$1';

$route[$r_inventory . '/product-size']                  	= 'inventory/product_size';
$route[$r_inventory . '/product-size/add']              	= 'inventory/product_size_form/add';
$route[$r_inventory . '/product-size/edit/(:num)']      	= 'inventory/product_size_form/edit/$1';

$route[$r_inventory . '/product-color']                 	= 'inventory/product_color';
$route[$r_inventory . '/product-color/add']             	= 'inventory/product_color_form/add';
$route[$r_inventory . '/product-color/edit/(:num)']     	= 'inventory/product_color_form/edit/$1';

$route[$r_inventory . '/raw-products']              = 'inventory/raw_products';
$route[$r_inventory . '/import-products']           = 'inventory/raw_products_form/import';
$route[$r_inventory . '/raw-products/add']          = 'inventory/raw_products_form/add';
$route[$r_inventory . '/raw-products/edit/(:num)']  = 'inventory/raw_products_form/edit/$1';

$route[$r_inventory . '/imp-purchase-order']  = 'inventory/purchase_order/import';
$route[$r_inventory . '/priority-po']         = 'inventory/priority_po';
$route[$r_inventory . '/loading-list-po']     = 'inventory/loading_list_po';
$route[$r_inventory . '/loading-list-po/add']     = 'inventory/loading_list_po_form/add';
$route[$r_inventory . '/po-purchase-in']     = 'inventory/po_purchase_in';

$route[$r_inventory . '/po-expense']          = 'inventory/po_expense';
$route[$r_inventory . '/po-expense/add']      = 'inventory/po_expense_form/add';
$route[$r_inventory . '/po-expense/edit/(:num)']  = 'inventory/po_expense_form/edit/$1';

$route[$r_inventory . '/payments']          = 'inventory/payments';
$route[$r_inventory . '/payments/add']      = 'inventory/payments_form/add';
$route[$r_inventory . '/payments/edit/(:num)']      = 'inventory/payments_form/edit/$1';

$route[$r_inventory . '/purchase-order'] = 'inventory/purchase_order/local';
$route[$r_inventory . '/purchase-order/add-import']    = 'inventory/purchase_order_form/add_import';
$route[$r_inventory . '/purchase-order/edit-import/(:num)'] = 'inventory/purchase_order_form/edit_import/$1';

$route[$r_inventory . '/purchase-order/add-local']    = 'inventory/purchase_order_form/add_local';

$route[$r_inventory . '/purchase-order-entry'] = 'inventory/purchase_order_entry';
$route[$r_inventory . '/purchase-order/edit/(:num)'] = 'inventory/purchase_order_form/edit/$1';
$route[$r_inventory . '/purchase-order-entry/view/(:num)'] = 'inventory/purchase_order_form/view_entry/$1';

$route[$r_inventory . '/purchase-reports'] = 'inventory/purchase_reports';
$route[$r_inventory . '/sales-reports'] = 'inventory/sales_reports';
$route[$r_inventory . '/sales-return-reports'] = 'inventory/sales_return_reports';
$route[$r_inventory . '/stock-reports'] = 'inventory/stock_reports';

$route[$r_inventory . '/purchase-entry'] = 'inventory/purchase_entry';
$route[$r_inventory . '/purchase-entry/add']    = 'inventory/purchase_entry_form/add';
$route[$r_inventory . '/purchase-entry/edit/(:num)'] = 'inventory/purchase_entry_form/edit/$1';

$route[$r_inventory . '/reserved-order'] = 'inventory/reserved_order';
$route[$r_inventory . '/reserved-order/add'] = 'inventory/reserved_order_form/add';

$route[$r_inventory . '/damage-stock'] = 'inventory/damage_stock';
$route[$r_inventory . '/damage-stock/add'] = 'inventory/damage_stock_form/add';
$route[$r_inventory . '/damage-stock/view/(:num)'] = 'inventory/damage_stock_form/view/$1';

$route[$r_inventory . '/damage-stock-product'] = 'inventory/damage_stock_product';
$route[$r_inventory . '/scrap-product'] = 'inventory/scrap_product';

$route[$r_inventory . '/my-stock'] = 'inventory/my_stock';
$route[$r_inventory . '/my-stock-batch/(:any)/(:any)'] = 'inventory/my_stock_batch/$1/$2';
$route[$r_inventory . '/my-stock-history/(:any)'] = 'inventory/my_stock_history/$1';
$route[$r_inventory . '/qc-pending'] = 'inventory/qc_pending';

$route[$r_inventory . '/stock-transfer'] = 'inventory/stock_transfer';
$route[$r_inventory . '/stock-transfer-list'] = 'inventory/stock_transfer_list';

$route[$r_inventory . '/goods-return'] 		= 'inventory/goods_return';
$route[$r_inventory . '/goods-return/add'] 	= 'inventory/goods_return_form/add';
$route[$r_inventory . '/goods-return/view/(:any)'] 	= 'inventory/goods_return_form/view/$1';

$route[$r_inventory . '/payment-reconceliation'] 		= 'inventory/payment_reconceliation';
$route[$r_inventory . '/payment-reconceliation/add'] 	= 'inventory/payment_reconceliation_form/add';
$route[$r_inventory . '/payment-reconceliation/view/(:any)'] 	= 'inventory/payment_reconceliation_form/view/$1';

/*Common*/
$route[$r_common . '/reminder']			    = 'common/reminder';
$route[$r_common . '/reminder-done']		= 'common/reminder_done';
$route[$r_common . '/reminder/add']    		= 'common/reminder_form/add';
$route[$r_common . '/reminder/edit/(:num)'] = 'common/reminder_form/edit/$1';

$route[$qc_inventory . '/raw-material'] = 'quality_control/raw_material';
$route[$qc_inventory . '/raw-material-done'] = 'quality_control/raw_material_done';

$route[$r_inventory . '/import-order'] = 'inventory/import_order';

$route[$r_inventory . '/import-purchase-order'] = 'inventory/import_purchase_order';

$route[$r_inventory . '/sales-order'] = 'inventory/sales_order';
$route[$r_inventory . '/sales-order/add']    = 'inventory/sales_order_form/add';
$route[$r_inventory . '/sales-order/view/(:num)']    = 'inventory/sales_order_form/view/$1';
$route[$r_inventory . '/sales-order/not-uploaded/(:num)']    = 'inventory/sales_order_form/excel/$1';
$route[$r_inventory . '/sales-order/products/(:num)']    = 'inventory/sales_order_form/products/$1';


$route[$r_inventory . '/purchase-return'] = 'inventory/purchase_return';
$route[$r_inventory . '/purchase-return/add'] = 'inventory/purchase_return_form/add';
$route[$r_inventory . '/purchase-return/view/(:any)'] 	= 'inventory/purchase_return_form/view/$1';

