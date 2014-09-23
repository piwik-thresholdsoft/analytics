<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/

$route['default_controller'] = "default_controller";
$route['404_override'] = '';
$route['test/(:any)'] = 'test/$1';


/* user transactions start */
$route['api/user/login'] = 'api/user/verifyUser';
$route['api/user/registration'] = 'api/user/addUser';
$route['api/user/(:any)/edit'] = 'api/user/updateUser/$1';
$route['api/user/(:any)/delete'] = 'api/user/deleteUser';
$route['api/user/(:any)'] = 'api/user/getUserDetails/$1';
$route['api/user/getUserId']='api/user/getUserId';
$route['api/user/logout']='api/user/logout';
/* user transactions end */

/* sites transactions start*/
$route['api/sites'] = (strtoupper ($_SERVER['REQUEST_METHOD']) == 'POST'  ? 'api/sites/addSite' :
    (strtoupper ($_SERVER['REQUEST_METHOD']) == 'GET'  ? 'api/sites/getSites' :
     (strtoupper ($_SERVER['REQUEST_METHOD']) == 'DELETE'  ? 'api/sites/deleteMultipleSites' :
            '')));

$route['api/sites/(:any)'] = (strtoupper ($_SERVER['REQUEST_METHOD']) == 'GET'  ? 'api/sites/getSiteDetails/$1' :
    (strtoupper ($_SERVER['REQUEST_METHOD']) == 'PUT'  ? 'api/sites/updateSite/$1' :
        (strtoupper ($_SERVER['REQUEST_METHOD']) == 'DELETE'  ? 'api/sites/deleteSite/$1' :
            '')));
/* sites transactions end*/

/* ecommerce transactions start*/

$route['api/goals'] = (strtoupper ($_SERVER['REQUEST_METHOD']) == 'POST'  ? 'api/ecommerce/addGoal' :
    (strtoupper ($_SERVER['REQUEST_METHOD']) == 'GET'  ? 'api/ecommerce/getGoals' :
    (strtoupper ($_SERVER['REQUEST_METHOD']) == 'DELETE'  ? 'api/ecommerce/deleteMultipleGoals' :
            '')));

$route['api/goals/(:any)'] = (strtoupper ($_SERVER['REQUEST_METHOD']) == 'GET'  ? 'api/ecommerce/getGoalDetails/$1' :
    (strtoupper ($_SERVER['REQUEST_METHOD']) == 'PUT'  ? 'api/ecommerce/updateGoal/$1' :
    (strtoupper ($_SERVER['REQUEST_METHOD']) == 'DELETE'  ? 'api/ecommerce/deleteGoal/$1' :
        '')));

/*ecommerce transaction end*/

/* analytics transaction start */
$route['api/analytics/all/(:any)'] = (strtoupper ($_SERVER['REQUEST_METHOD']) == 'GET'  ? 'api/analytics/identifyGoal/$1' :'');
$route['api/analytics/ecommerce/(:any)'] = (strtoupper ($_SERVER['REQUEST_METHOD']) == 'GET'  ? 'api/analytics/getEcommerceOrders/$1' :'');

/* analytics transaction end /*




$route['api/(:any)'] = 'api/$1';

/* End of file routes.php */
/* Location: ./application/config/routes.php */