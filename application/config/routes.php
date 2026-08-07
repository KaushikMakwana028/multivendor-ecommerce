<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'Home';

$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

/*
|--------------------------------------------------------------------------
| Public Pages
|--------------------------------------------------------------------------
*/
$route['privacy_policy']   = 'Pages/privacy_policy';
$route['terms_conditions'] = 'Pages/terms_conditions';
$route['terms_condition']  = 'Pages/terms_conditions';
$route['terms_and_conditions'] = 'Pages/terms_conditions';
$route['terms_and_condition']  = 'Pages/terms_conditions';
$route['refund_policy']    = 'Pages/refund_policy';
$route['refund_policies']  = 'Pages/refund_policy';
$route['delete_account']   = 'Pages/delete_account';

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/
$route['login']    = 'Login/login';
$route['admin']    = 'Login/login';
$route['register'] = 'Login/register';
$route['logout']   = 'Login/logout';
$route['profile']  = 'Dashboard/profile';

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/
$route['dashboard'] = 'Dashboard/index';

/*
|--------------------------------------------------------------------------
| Policy Pages Admin
|--------------------------------------------------------------------------
*/
$route['admin/pages'] = 'Admin_pages/index';
$route['admin/pages/help-support'] = 'Admin_pages/help_support';
$route['admin/pages/help-support/update'] = 'Admin_pages/update_help_support';
$route['admin/pages/edit/(:num)'] = 'Admin_pages/edit/$1';
$route['admin/pages/update/(:num)'] = 'Admin_pages/update/$1';


/*
|--------------------------------------------------------------------------
| Categories
|--------------------------------------------------------------------------
*/
$route['category']               = 'Category/index';
$route['category/add']           = 'Category/add';
$route['category/store']         = 'Category/store';
$route['category/edit/(:num)']   = 'Category/edit/$1';
$route['category/update/(:num)'] = 'Category/update/$1';
$route['category/delete/(:num)'] = 'Category/delete/$1';

/*
|--------------------------------------------------------------------------
| Products
|--------------------------------------------------------------------------
*/
$route['product']               = 'Product/index';
$route['product/add']           = 'Product/add';
$route['product/store']         = 'Product/store';
$route['product/edit/(:num)']   = 'Product/edit/$1';
$route['product/update/(:num)'] = 'Product/update/$1';
$route['product/delete/(:num)'] = 'Product/delete/$1';

/*
|--------------------------------------------------------------------------
| Orders
|--------------------------------------------------------------------------
*/
$route['order']               = 'Order/index';
$route['orders']              = 'Order/index';
$route['order/view/(:num)']   = 'Order/view/$1';
$route['orders/view/(:num)']  = 'Order/view/$1';
$route['order/print_label/(:num)']   = 'Order/print_label/$1';
$route['order/print_invoice/(:num)'] = 'Order/print_invoice/$1';
$route['order/delete_order/(:num)']  = 'Order/delete_order/$1';

/*
|--------------------------------------------------------------------------
| API
|--------------------------------------------------------------------------
*/

$route['api/register_user'] = 'api/register_user';
$route['api/send_otp']    = 'api/send_otp';
$route['api/verify_otp']  = 'api/verify_otp';
$route['api/logout'] = 'api/logout_user';
$route['api/logout_user'] = 'api/logout_user';
$route['api/get_my_profile']                  = 'api/get_my_profile';
$route['api/update_my_profile']               = 'api/update_my_profile';
$route['api/get_category_list']               = 'api/get_category_list';
$route['api/get_category_detail/(:num)']      = 'api/get_category_detail/$1';
$route['api/get_products_by_category/(:num)'] = 'api/get_products_by_category/$1';
$route['api/get_product_list']                = 'api/get_product_list';
$route['api/get_product_detail/(:num)']       = 'api/get_product_detail/$1';
$route['api/add_to_cart']                     = 'api/add_to_cart';
$route['api/get_cart']                        = 'api/get_cart';
$route['api/update_cart_quantity']            = 'api/update_cart_quantity';
$route['api/remove_from_cart']                = 'api/remove_from_cart';
$route['api/clear_cart']                      = 'api/clear_cart';
$route['api/get_addresses']                   = 'api/get_addresses';
$route['api/save_address']                    = 'api/save_address';
$route['api/update_address']                  = 'api/update_address';
$route['api/delete_address']                  = 'api/delete_address';
$route['api/delete_account']                  = 'api/delete_account';
$route['api/privacy_policy']                  = 'api/privacy_policy';
$route['api/terms_conditions']                = 'api/terms_conditions';
$route['api/terms_condition']                 = 'api/terms_conditions';
$route['api/refund_policy']                   = 'api/refund_policy';
$route['api/refund_policies']                 = 'api/refund_policy';
$route['api/help_support']                    = 'api/help_support';
$route['api/place_order']                    = 'api/place_order';
$route['api/verify_order_payment']           = 'api/verify_order_payment';
$route['api/get_orders']                     = 'api/get_orders';
$route['api/get_order_details/(:num)']       = 'api/get_order_details/$1';
$route['api/cancel_order']                   = 'api/cancel_order';
$route['api/delete_order']                   = 'api/delete_order';
$route['api/check_delivery_charge']          = 'api/check_delivery_charge';
$route['api/track_order']          = 'api/track_order';
$route['live_status_webhook']          = 'api/shiprocket_webhook';
