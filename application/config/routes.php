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
$route['default_controller'] = 'Login';

$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/
$route['login']    = 'Login/login';
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
| API
|--------------------------------------------------------------------------
*/

$route['api/send_otp']    = 'api/send_otp';
$route['api/verify_otp']  = 'api/verify_otp';
$route['api/register_user'] = 'api/register_user';
$route['api/logout_user'] = 'api/logout_user';
$route['api/get_my_profile']                  = 'api/get_my_profile';
$route['api/update_my_profile']               = 'api/update_my_profile';
$route['api/get_category_list']               = 'api/get_category_list';
$route['api/get_category_detail/(:num)']      = 'api/get_category_detail/$1';
$route['api/get_products_by_category/(:num)'] = 'api/get_products_by_category/$1';
$route['api/get_product_list']                = 'api/get_product_list';
$route['api/get_product_detail/(:num)']       = 'api/get_product_detail/$1';
