<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Root: show login if not authenticated, otherwise redirect to home.
// Both GET and POST handled by index() to avoid routing issues.
$routes->match(['get', 'post'], '/', 'AuthController::index');
$routes->get('login', 'AuthController::index'); // Redirect /login to same as /
$routes->get('logout', 'AuthController::logout', ['filter' => 'auth']);
$routes->get('profile', 'AuthController::profile', ['filter' => 'auth']);
$routes->get('profile/reset-password', 'AuthController::resetOwnPassword', ['filter' => 'auth']);
$routes->post('profile/reset-password', 'AuthController::resetOwnPassword', ['filter' => 'auth']);

// Admin routes (Super Admin only)
$routes->group('admin', ['filter' => 'auth'], static function (RouteCollection $routes): void {
    $routes->get('users', 'AdminController::users', ['filter' => 'require_super_admin']);
    $routes->get('users/add', 'AdminController::addUser', ['filter' => 'require_super_admin']);
    $routes->post('users/add', 'AdminController::addUser', ['filter' => 'require_super_admin']);
    $routes->get('users/edit/(:num)', 'AdminController::userEdit/$1', ['filter' => 'require_super_admin']);
    $routes->post('users/edit/(:num)', 'AdminController::userEdit/$1', ['filter' => 'require_super_admin']);
    $routes->post('users/(:num)/reset-password', 'AdminController::resetUserPassword/$1', ['filter' => 'require_super_admin']);
    $routes->post('users/(:num)/reporting-manager', 'AdminController::setReportingManager/$1', ['filter' => 'require_super_admin']);
    $routes->post('users/(:num)/toggle-active', 'AdminController::toggleActive/$1', ['filter' => 'require_super_admin']);
    $routes->get('products/manage', 'AdminController::productsManage', ['filter' => 'require_super_admin']);
    $routes->get('products/add', 'AdminController::productAdd', ['filter' => 'require_super_admin']);
    $routes->post('products/add', 'AdminController::productAdd', ['filter' => 'require_super_admin']);
    $routes->get('products/edit/(:num)', 'AdminController::productEdit/$1', ['filter' => 'require_super_admin']);
    $routes->post('products/edit/(:num)', 'AdminController::productEdit/$1', ['filter' => 'require_super_admin']);
    $routes->post('products/delete/(:num)', 'AdminController::productDelete/$1', ['filter' => 'require_super_admin']);
    $routes->post('products/(:num)/toggle-disabled', 'AdminController::productToggleDisabled/$1', ['filter' => 'require_super_admin']);
    $routes->post('products/(:num)/members/add', 'AdminController::productMemberAdd/$1', ['filter' => 'require_super_admin']);
    $routes->post('products/(:num)/members/remove/(:num)', 'AdminController::productMemberRemove/$1/$2', ['filter' => 'require_super_admin']);
});

// Home (authenticated only - dashboard after login)
$routes->get('home', 'Home::index', ['filter' => 'auth']);

// RTMS: Products, Tasks, Timesheet
$routes->get('products', 'ProductController::index', ['filter' => 'auth']);
$routes->get('products/view/(:num)', 'ProductController::view/$1', ['filter' => 'auth']);
$routes->post('products/sync/(:num)', 'ProductController::syncFromGitHub/$1', ['filter' => 'auth']);
$routes->post('products/(:num)/tasks/add', 'ProductController::taskAdd/$1', ['filter' => ['auth', 'require_manager']]);
$routes->get('products/(:num)/tasks/edit/(:num)', 'ProductController::taskEdit/$1/$2', ['filter' => ['auth', 'require_manager']]);
$routes->post('products/(:num)/tasks/edit/(:num)', 'ProductController::taskEdit/$1/$2', ['filter' => ['auth', 'require_manager']]);
$routes->post('products/(:num)/tasks/delete/(:num)', 'ProductController::taskDelete/$1/$2', ['filter' => ['auth', 'require_manager']]);
$routes->get('tasks', 'TaskController::index', ['filter' => 'auth']);
$routes->get('timesheet', 'TimesheetController::index', ['filter' => 'auth']);
$routes->get('timesheet/sheet', 'TimesheetController::sheetView', ['filter' => 'auth']);
$routes->get('timesheet/view', 'TimesheetController::viewSummary', ['filter' => 'auth']);
$routes->get('timesheet/team', 'TimesheetController::teamView', ['filter' => ['auth', 'require_product_lead_or_manager']]);
$routes->get('timesheet/team/details', 'TimesheetController::teamDetails', ['filter' => ['auth', 'require_product_lead_or_manager']]);
$routes->get('timesheet/edit/(:num)', 'TimesheetController::edit/$1', ['filter' => 'auth']);
$routes->post('timesheet/update/(:num)', 'TimesheetController::update/$1', ['filter' => 'auth']);
$routes->post('timesheet/log', 'TimesheetController::log', ['filter' => 'auth']);

// Milestones
$routes->get('milestones', 'MilestoneController::index', ['filter' => 'auth']);

// Approval (Manager + Product Lead for timesheet; Manager for task completion)
$routes->get('approval', 'ApprovalController::index', ['filter' => ['auth', 'require_product_lead_or_manager']]);
$routes->post('approval/approve/(:num)', 'ApprovalController::approve/$1', ['filter' => ['auth', 'require_product_lead_or_manager']]);
$routes->post('approval/reject/(:num)', 'ApprovalController::reject/$1', ['filter' => ['auth', 'require_product_lead_or_manager']]);
$routes->post('approval/timesheet/approve/(:num)', 'ApprovalController::approveTimesheet/$1', ['filter' => ['auth', 'require_product_lead_or_manager']]);

// Costing (Manager only)
$routes->get('costing', 'CostingController::index', ['filter' => ['auth', 'require_manager']]);
$routes->post('costing/save', 'CostingController::save', ['filter' => ['auth', 'require_manager']]);

// Reports (Finance, Manager)
$routes->get('reports', 'ReportController::index', ['filter' => ['auth', 'require_finance_or_manager']]);
$routes->get('reports/task-wise', 'ReportController::taskWise', ['filter' => ['auth', 'require_finance_or_manager']]);
$routes->get('reports/employee-wise', 'ReportController::employeeWise', ['filter' => ['auth', 'require_finance_or_manager']]);
$routes->get('reports/performance', 'ReportController::performance', ['filter' => ['auth', 'require_finance_or_manager']]);
$routes->get('reports/export/task-wise', 'ReportController::exportTaskWise', ['filter' => ['auth', 'require_finance_or_manager']]);

$routes->setAutoRoute(false);
