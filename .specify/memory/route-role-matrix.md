# Route-Role Matrix: RTMS

**Created**: 2026-02-19  
**Purpose**: Document which routes require which roles (T010)

---

## Public Routes (No Auth)

| Route | Controller | Notes |
|-------|------------|-------|
| `/` | AuthController::index | Login (GET) or login handler (POST) |
| `/login` | AuthController::index | Same as / |

---

## Authenticated Routes (Auth Required)

| Route | Controller | Filter |
|-------|------------|--------|
| `/logout` | AuthController::logout | auth |
| `/profile` | AuthController::profile | auth |
| `/profile/reset-password` | AuthController::resetOwnPassword | auth |

---

## Manager + Super Admin

| Route | Controller | Filter |
|-------|------------|--------|
| `/admin/dashboard` | AdminController::dashboard | auth, require_manager |

---

## Super Admin Only

| Route | Controller | Filter |
|-------|------------|--------|
| `/admin/users` | AdminController::users | auth, require_super_admin |
| `/admin/users/add` | AdminController::addUser | auth, require_super_admin |
| `/admin/users/edit/(:num)` | AdminController::userEdit | auth, require_super_admin |
| `POST /admin/users/edit/(:num)` | AdminController::userEdit | auth, require_super_admin |
| `POST /admin/users/(:num)/reset-password` | AdminController::resetUserPassword | auth, require_super_admin |
| `POST /admin/users/(:num)/reporting-manager` | AdminController::setReportingManager | auth, require_super_admin |
| `POST /admin/users/(:num)/toggle-active` | AdminController::toggleActive | auth, require_super_admin |
| `/admin/products/manage` | AdminController::productsManage | auth, require_super_admin |
| `/admin/products/add` | AdminController::productAdd | auth, require_super_admin |
| `/admin/products/edit/(:num)` | AdminController::productEdit | auth, require_super_admin |
| `POST /admin/products/delete/(:num)` | AdminController::productDelete | auth, require_super_admin |
| `POST /admin/products/(:num)/toggle-disabled` | AdminController::productToggleDisabled | auth, require_super_admin |
| `POST /admin/products/(:num)/members/add` | AdminController::productMemberAdd | auth, require_super_admin |
| `POST /admin/products/(:num)/members/remove/(:num)` | AdminController::productMemberRemove | auth, require_super_admin |

---

## RTMS Routes (Implemented)

| Route | Controller | Filter |
|-------|------------|--------|
| `/home` | Home::index | auth |
| `/products` | ProductController::index | auth |
| `/products/view/(:num)` | ProductController::view | auth |
| `POST /products/sync/(:num)` | ProductController::syncFromGitHub | auth |
| `POST /products/(:num)/tasks/add` | ProductController::taskAdd | auth, require_manager |
| `/products/(:num)/tasks/edit/(:num)` | ProductController::taskEdit | auth, require_manager |
| `POST /products/(:num)/tasks/delete/(:num)` | ProductController::taskDelete | auth, require_manager |
| `/tasks` | TaskController::index | auth |
| `/timesheet` | TimesheetController::index | auth |
| `/timesheet/sheet` | TimesheetController::sheetView | auth |
| `/timesheet/view` | TimesheetController::viewSummary | auth |
| `/timesheet/team` | TimesheetController::teamView | auth, require_product_lead_or_manager (supports ?team= department filter) |
| `/timesheet/team/details` | TimesheetController::teamDetails | auth, require_product_lead_or_manager |
| `/timesheet/edit/(:num)` | TimesheetController::edit | auth |
| `POST /timesheet/update/(:num)` | TimesheetController::update | auth |
| `POST /timesheet/log` | TimesheetController::log | auth |
| `/milestones` | MilestoneController::index | auth |
| `/approval` | ApprovalController::index | auth, require_product_lead_or_manager |
| `POST /approval/approve/(:num)` | ApprovalController::approve | auth, require_product_lead_or_manager |
| `POST /approval/reject/(:num)` | ApprovalController::reject | auth, require_product_lead_or_manager |
| `POST /approval/timesheet/approve/(:num)` | ApprovalController::approveTimesheet | auth, require_product_lead_or_manager |
| `POST /approval/timesheet/reject/(:num)` | ApprovalController::rejectTimesheet | auth, require_product_lead_or_manager |
| `/costing` | CostingController::index | auth, require_manager |
| `POST /costing/save` | CostingController::save | auth, require_manager |
| `/reports` | ReportController::index | auth, require_finance_or_manager |
| `/reports/task-wise` | ReportController::taskWise | auth, require_finance_or_manager |
| `/reports/employee-wise` | ReportController::employeeWise | auth, require_finance_or_manager |
| `/reports/performance` | ReportController::performance | auth, require_finance_or_manager |
| `/reports/export/task-wise` | ReportController::exportTaskWise | auth, require_finance_or_manager |

---

## Future Routes

| Route | Filter | Notes |
|-------|--------|-------|
| Webhook endpoint | public | GitHub push/merge events |
| Export PDF, Excel | auth, require_finance_or_manager | TODO |
| Forgot Password | public | Password recovery flow |

---

**Version**: 1.8.0 | **Updated**: 2026-02-19 (Admin dashboard, department filter, leave products)
