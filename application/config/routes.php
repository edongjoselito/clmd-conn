<?php
defined('BASEPATH') OR exit('No direct script access allowed');

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
$route['default_controller'] = 'pages/view';

$route['log_in'] = 'pages/log_in';
$route['signup'] = 'pages/signup';
$route['signup_district'] = 'pages/signup_district';
$route['logout'] = 'pages/logout';
$route['lock'] = 'pages/lock';
$route['homepage'] = 'pages/homepage';
$route['password-change-required'] = 'pages/password_change_required';

$route['lock_user_screen'] = 'pages/lock_user_screen';


// Scope dashboards. The site root dispatches each role to its own level so a
// dashboard URL names the scope it shows. Access is enforced by $role_policies
// in Pages::__construct(); admin is deliberately served at the root instead.
$route['region'] = 'pages/region';
$route['division'] = 'pages/division';
$route['district'] = 'pages/district';
// Bare 'school' is the dashboard; 'school/<id>' remains the school profile.
// ':any' compiles to [^/]+, so the two patterns never overlap.
$route['school'] = 'pages/school_dashboard';
$route['school/(:any)'] = 'pages/school/$1';

$route['pages/get_provinces'] = 'pages/get_provinces';
$route['pages/get_districts'] = 'pages/get_districts';



$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['monitoring-tool/settings/indicators'] = 'pages/monitoring_indicators';
$route['monitoring/tool'] = 'pages/monitoring_tool';

$route['monitoring-tool/settings/tools'] = 'pages/monitoring_tools';

$route['learning-area/(:any)'] = 'pages/learning_area_dashboard/$1';

$route['focal-persons'] = 'pages/focal_persons';
$route['focal-persons/new'] = 'pages/focal_person_edit';
$route['focal-persons/(:num)/edit'] = 'pages/focal_person_edit/$1';
$route['focal-persons/(:num)/delete'] = 'pages/focal_person_delete/$1';
