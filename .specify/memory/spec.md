# Baseline Specification: Resource Timesheet Management System (RTMS)

**Created**: 2026-02-19  
**Status**: In progress (Time Sheet grid, Team Timesheet format, Reports filter, Manage Users/Products enhancements implemented)  
**Constitution**: v1.6.0  
**Input**: Role capabilities, product workflows, task lifecycle, reporting structure

---

**Context:**
- Project was NOT built using Spec Kit
- Base framework (PHP/CodeIgniter/Smarty) exists; RTMS features (products, tasks, timesheets, GitHub, approval) need to be built fresh
- Spec Kit is used only to safely extend functionality

**Clarifications** (from clarify.md):
- Only Manager can approve tasks; Product Lead cannot
- Only Manager can see financial costing
- GitHub sync: Webhooks (real-time); Issues + PRs; one branch per task; Employee selects branch from list
- Build from scratch: new schema, new roles
- **Login**: Individual users must log in via a dedicated login page. Disabled users (is_active=0) cannot log in.
- **Auth features**: Logout; user profile (Name, email, current role, team name, reporting manager); user self-service password reset; Super Admin can reset any user's password, add new users, enable/disable users, modify reporting manager.
- **Timesheet workflow**: Submitted timesheets are pending_approval; employee can edit before approval; success message on submit.
- **Approvals**: Product Lead can approve timesheets for their product members; Manager approves for direct reports (reporting_manager_id); time entries routed to mapped lead/manager.

---

## 1. Role Definitions & Capabilities

### 1.1 Product Lead

**Can Do**:
- Add or remove members to product
- Integrate GitHub repository to product
- Create tasks and milestones
- Assign tasks to product members
- Monitor task progress

**Cannot Do**:
- Modify system-wide users or roles
- See financial costing (only Manager allowed)
- Approve task completion (Manager only)

**Can Do (Timesheet)**:
- Approve timesheet entries for members of products they lead
- View consolidated timesheet (team timesheet) for their product members by daily/weekly/monthly

### 1.2 Manager (Admin)

**Can Do**: All Product Lead capabilities plus:
- Approve task completion (multi-level approval; Manager is the approver)
- Approve timesheet entries for direct reports (users where reporting_manager_id = Manager)
- System-wide user/role management
- View and manage financial costing
- Create, delete, modify tasks (within products they manage)
- View consolidated timesheet (team timesheet) for their reports by daily/weekly/monthly

### 1.3 Employee

**Can Do**:
- View assigned products and tasks
- Change task status (To Do → In Progress → Completed)
- Link GitHub branch to assigned task
- Push commits to linked branch
- Log time taken to complete task
- Respond to rework requests

**Cannot Do**:
- Create products or tasks
- Assign tasks to others
- View other employees' performance
- Edit completed or approved task timing

### 1.4 Finance

**Can Do**:
- View employee-wise performance metrics
- View task-wise and project-wise time consumption
- View rework impact
- Generate performance and productivity reports
- Analyze efficiency vs time spent

**Cannot Do**:
- Create or modify products or tasks
- Approve or change task status

### 1.5 Super Admin

**Can Do**:
- All Manager capabilities
- Add new users with: username, email, first name, last name, current role (selection), team name (selection), phone number
- Reset any user's password
- Enable or disable users (disabled users cannot log in)
- Modify reporting manager (reporting_manager_id) for any user
- Product CRUD: add, edit, delete, rename product; delete blocked if users/tasks mapped
- Grant/revoke product access to manager or product lead (product_members UI)
- Task CRUD: create, delete, modify task (with Manager); delete blocked if time entries mapped
- Success/error messages for all product and task CRUD operations

**Cannot Do**:
- None (full system access)

---

## 1.6 User Profile & Auth (All Roles)

- **User Profile**: Each user has a profile view showing Name, email, current role, team name, reporting manager. Accessible to the logged-in user.
- **Logout**: All users can log out from the application.
- **Password Reset (self)**: Any user can reset their own password (requires current password verification or forgot-password flow).
- **Password Reset (Super Admin)**: Super Admin can reset any other user's password without knowing the current password.

---

## 2. Product-Level Workflows

### 2.1 Product Creation Flow

1. Manager / Product Lead → Create Product
2. Add Members to Product
3. Define Timeline (Start & End Date)
4. Set Maximum Allowed Time
5. Link GitHub Repository

### 2.2 Task Lifecycle Flow

1. Create Task
2. Assign Task to Employee
3. Employee Links GitHub Branch
4. Employee Starts Work

### 2.3 Task Execution Flow

1. **Task Status: To Do**
2. First Commit Pushed → **In Progress**
3. Employee Logs Time
4. Branch Merged to Main → **Completed**

---

## 3. User Scenarios & Testing

### User Story 0 - User Profile, Logout & Password Management (Priority: P1)

As a logged-in user, I can view my profile (Name, email, current role, team name), log out, and reset my password so I can manage my account. As Super Admin, I can add new users and reset any user's password.

**Acceptance Scenarios**:
1. **Given** I am logged in, **When** I click Profile, **Then** I see Name, email, current role, team name
2. **Given** I am logged in, **When** I click Logout, **Then** I am logged out and redirected to login
3. **Given** I am logged in, **When** I submit "Reset my password" with current + new password, **Then** my password is updated
4. **Given** I am Super Admin, **When** I select a user and set a new password, **Then** that user's password is reset without needing current password
5. **Given** I am Super Admin, **When** I add a new user (username, email, first name, last name, role, team, phone), **Then** the user is created and can log in

---

### User Story 1 - Product Lead Creates Product and Onboards Team (Priority: P1)

As a Product Lead, I create a product, add members, set timeline and max allowed time, and link a GitHub repository so the team can start working on tasks.

**Why this priority**: Foundation for all product-based work; no products means no tasks or timesheets.

**Independent Test**: Create product, add 2 members, set dates, link repo; verify product appears for assigned members.

**Acceptance Scenarios**:
1. **Given** I am logged in as Product Lead, **When** I create a product with name and timeline, **Then** the product is created and I can add members
2. **Given** a product exists, **When** I add members and link a GitHub repo, **Then** members see the product and tasks can sync from GitHub
3. **Given** I am Employee, **When** I am added to a product, **Then** I see the product and its tasks in my dashboard

---

### User Story 2 - Employee Executes Task and Logs Time (Priority: P1)

As an Employee, I view my assigned tasks, change status (To Do → In Progress → Completed), link a GitHub branch, push commits, and log time spent on the task.

**Why this priority**: Core value delivery; tasks without execution and time logging provide no measurable output.

**Independent Test**: Assign task to employee; employee links branch, pushes commit, logs time; verify status transitions and time is recorded.

**Acceptance Scenarios**:
1. **Given** I have an assigned task in To Do, **When** I link my GitHub branch and push first commit, **Then** task status becomes In Progress
2. **Given** task is In Progress, **When** I log time spent, **Then** time is recorded and subject to D+N policy
3. **Given** I merge branch to main, **When** I mark task completed, **Then** task status becomes Completed
4. **Given** task is completed or approved, **When** I attempt to edit task timing, **Then** system prevents modification (BR-3)

---

### User Story 3 - Manager Approves Task Completion (Priority: P2)

As a Manager, I approve task completion so work is formally acknowledged and locked for reporting.

**Why this priority**: Approval closes the loop and enables reporting; tasks can exist without approval but approval enables performance and costing analysis. Only Manager can approve (Product Lead cannot).

**Independent Test**: Employee completes task; Manager approves; verify approval is recorded and task is locked.

**Acceptance Scenarios**:
1. **Given** task is Completed, **When** Manager approves, **Then** approval is logged and task is locked
2. **Given** approval is recorded, **When** anyone attempts to edit task timing, **Then** system rejects (BR-3)
3. **Given** I am Product Lead or Employee, **When** I attempt to approve a task, **Then** system denies (RBAC—only Manager can approve)

---

### User Story 4 - Finance Views Reports and Performance Metrics (Priority: P2)

As a Finance user, I view employee-wise and task-wise metrics, rework impact, and generate performance and productivity reports to analyze efficiency.

**Why this priority**: Business intelligence; enables costing and efficiency decisions after core execution is in place.

**Independent Test**: Log time on several tasks; Finance views reports; verify task-wise time, rework %, and performance metrics are visible.

**Acceptance Scenarios**:
1. **Given** tasks have logged time, **When** Finance views task-wise time consumption, **Then** report shows time per task and project
2. **Given** rework has been logged, **When** Finance views rework impact, **Then** Rework % is calculated per BR-4
3. **Given** I am Finance, **When** I attempt to approve or change task status, **Then** system denies (RBAC)

---

### User Story 5 - Employee Responds to Rework Requests (Priority: P3)

As an Employee, I receive rework requests (e.g., task reopened or correction needed) and respond by logging additional time; system tracks rework for quality metrics.

**Why this priority**: Quality and cost visibility; rework tracking feeds performance scoring.

**Independent Test**: Task reopened; employee logs rework time; verify Rework % is calculated (BR-4).

**Acceptance Scenarios**:
1. **Given** task is reopened for rework, **When** Employee logs additional time, **Then** time is tagged as rework
2. **Given** rework time is logged, **When** report is generated, **Then** Rework % = (Rework Hours / Total Hours) × 100 (BR-4)

---

### Edge Cases (Clarified)

- **Branch already linked**: One branch per task; system rejects linking a branch already linked to another task
- **GitHub sync failures**: Disable sync until fixed; surface error to admin
- **Timeline exceeded**: Show warning only; allow logging (clarify Q6.2)
- **Employee removed from product**: Reassign assigned tasks to Product Lead
- **Maximum allowed time exceeded**: No block; warn or audit only

---

## 4. Functional Requirements

### Role & Access

- **FR-000**: System MUST provide a login page for individual user authentication. All timesheet logging, product/task management, and reports require authenticated session. Unauthenticated users are redirected to login.
- **FR-000a**: System MUST provide logout functionality for all authenticated users.
- **FR-000b**: System MUST provide a user profile page showing Name, email, current role, team name for the logged-in user.
- **FR-000c**: System MUST allow any user to reset their own password (self-service).
- **FR-000d**: System MUST allow Super Admin to reset any user's password.
- **FR-000e**: System MUST allow Super Admin to add new users with: username, email, first name, last name, current role (selection), team name (selection), phone number.
- **FR-000f**: System MUST allow Super Admin to enable/disable users; disabled users (is_active=0) cannot log in.
- **FR-000g**: System MUST allow Super Admin to modify reporting manager (reporting_manager_id) for any user.
- **FR-001**: System MUST enforce RBAC for Employee, Product Lead, Manager, Finance, and Super Admin roles per capability matrix
- **FR-002**: System MUST prevent Employees from creating products, assigning tasks, or viewing other employees' performance
- **FR-003**: System MUST prevent Finance from creating/modifying products or tasks, or approving/changing task status
- **FR-004**: System MUST prevent Product Lead from modifying system-wide users or roles
- **FR-005**: System MUST restrict financial costing visibility to Manager only (Product Lead and Finance cannot see costing)

### Product & Membership

- **FR-005a**: System MUST allow Super Admin to add, edit, delete, rename products; disable/enable products (is_disabled); delete blocked if users or tasks mapped; show success/error messages; disabled products excluded from main product list
- **FR-005b**: System MUST allow Super Admin to grant/revoke product access to manager or product lead via product_members UI
- **FR-006**: System MUST allow Product Lead/Manager to create products with name, timeline (start/end date), and maximum allowed time
- **FR-007**: System MUST allow Product Lead/Manager to add or remove members to/from a product
- **FR-008**: System MUST allow Product Lead/Manager to link a GitHub repository to a product

### Tasks & Assignment

- **FR-008a**: System MUST allow Manager and Super Admin to create, delete, modify tasks; delete blocked if time entries mapped; show success/error messages
- **FR-009**: System MUST allow Product Lead/Manager to create tasks and milestones
- **FR-010**: System MUST allow Product Lead/Manager to assign tasks to product members
- **FR-011**: System MUST allow Employee to link a GitHub branch to an assigned task; system lists branches from GitHub API; Employee selects one; one branch per task (no reuse across tasks)
- **FR-012**: System MUST track task status: To Do, In Progress, Completed
- **FR-013**: System MUST transition task to In Progress when first commit is pushed to linked branch (detected via GitHub Webhooks)
- **FR-014**: System MUST transition task to Completed when branch is merged to main (or equivalent completion signal)
- **FR-015**: System MUST prevent editing of completed or approved task timing (BR-3)

### Time & Rework

- **FR-016**: System MUST allow Employee to log time taken to complete a task
- **FR-017**: System MUST enforce D+N policy for timesheet editability (BR-2)
- **FR-018**: System MUST enforce configurable daily hours limit (BR-1)
- **FR-019**: System MUST allow rework time to be logged (Employee explicitly marks time entry as rework when logging) and calculate Rework % = (Rework Hours / Total Hours) × 100 (BR-4)
- **FR-020**: System MUST allow Employee to respond to rework requests (log rework time)
- **FR-020a**: System MUST show warning when logging time for tasks in products past End Date; MUST allow logging (no block)

### Timesheet Workflow & Approval

- **FR-020b**: System MUST show success message when employee submits timesheet
- **FR-020c**: System MUST store time entries with status (pending_approval, approved); submitted entries are pending_approval
- **FR-020d**: System MUST allow employee to edit time entry before approval (while status = pending_approval)
- **FR-020e**: System MUST provide timesheet view by period (daily, weekly, monthly) with Project Name | Time entered | Total submitted
- **FR-020f**: System MUST map employee to reporting manager (reporting_manager_id); time entries routed to mapped lead/manager for approval
- **FR-020g**: System MUST allow Product Lead to approve timesheet entries for members of products they lead
- **FR-020h**: System MUST allow Manager to approve timesheet entries for direct reports (reporting_manager_id)
- **FR-020i**: System MUST show consolidated timesheet (team timesheet) to Lead/Manager for their respective reports
- **FR-020j**: System MUST provide Time Sheet grid view (/timesheet/sheet): tasks as rows, week days as columns; hours per task per day; row totals, daily totals, weekly total; budget progress (used/max h) per product
- **FR-020k**: System MUST show Team Timesheet in resource allocation format: one row per employee with Employee Name, Department, Allocation, Billing Role, Billing Rate, Hours Spent/Allocated (progress bar), Actions (View); date pickers (daily/weekly/monthly); /timesheet/team/details for member entries
- **FR-021**: System MUST allow Manager and Product Lead to approve (task completion: Manager; timesheet: Manager for reports, Product Lead for product members)
- **FR-022**: System MUST lock timesheet/task entries after final approval
- **FR-023**: System MUST provide Finance with employee-wise and task-wise time consumption reports; reports filter by date range (From/To) selectable on Reports page
- **FR-024**: System MUST provide Finance with rework impact and performance/productivity reports; reports viewable in UI and exportable (CSV, PDF, Excel)
- **FR-025**: System MUST allow Finance to analyze efficiency vs time spent

### Integration

- **FR-026**: System MUST integrate with GitHub via Webhooks for repository linking, sync of Issues + PRs, branch tracking, and commit/merge detection; disable sync on failure until fixed
- **FR-027**: System MUST monitor task progress (e.g., status, time logged, commits)

---

## 5. Key Entities

- **User**: username, email, first_name, last_name, phone, password (hashed), role_id, team_id, reporting_manager_id, is_active
- **Team**: id, name (team name for grouping users)
- **Product**: Represents a deliverable or project; has name, timeline, max allowed time, GitHub repo link, members, is_disabled; disabled products excluded from main product list
- **Task**: Represents work unit; has status (To Do/In Progress/Completed), assignment, linked branch, time log, rework log
- **Milestone**: Time-bounded deliverable; can group tasks; has release status
- **Member**: User assigned to a product; role within product context
- **TimeEntry**: Logged time against a task; status (pending_approval, approved); subject to D+N policy; tagged as regular or rework; editable by employee while pending_approval
- **Approval**: Logical approval of task completion; locks task timing; records approver and timestamp
- **GitHubLink**: Repository or branch linked to product/task; used for sync and commit/merge detection

---

## 6. Success Criteria

### Measurable Outcomes

- **SC-001**: Product Lead can create a product, add members, and link GitHub repo within 5 minutes
- **SC-002**: Employee can link branch, log time, and complete task with status transitions reflecting actual Git activity
- **SC-003**: Finance can generate task-wise and employee-wise reports reflecting logged time and rework %
- **SC-004**: No role can perform actions outside their defined Can Do list (100% RBAC enforcement)
- **SC-005**: Completed or approved task timing cannot be modified (BR-3 enforced)
- **SC-006**: Rework % is calculated correctly per BR-4 for all tasks with rework

---

---

## 7. Implementation Status (2026-02-19)

**Scope**: Timesheet workflow, reporting structure, product/task CRUD, access control, Vertex UI, user enable/disable.

### Implemented

| Area | Status | Notes |
|------|--------|-------|
| **Time Entry** | ✓ | TimesheetController; log time; work_date, hours, is_rework, status; BR-1; success message on submit; redirect to daily summary after log |
| **Timesheet Workflow** | ✓ | status pending_approval/approved; edit before approval; timesheet/view (daily/weekly/monthly) with Time Entry Details table; timesheet/edit |
| **Reporting Structure** | ✓ | users.reporting_manager_id; time entries routed to mapped lead/manager; profile shows reporting manager |
| **Approval** | ✓ | Product Lead + Manager + Super Admin; task completion + timesheet approvals; ApprovalController::approveTimesheet |
| **Team Timesheet** | ✓ | /timesheet/team; resource allocation format (Employee, Dept, Allocation, Billing, Hours Spent/Allocated); /timesheet/team/details for member entries |
| **Products** | ✓ | list, view, sync; Super Admin: add/edit/delete/disable-enable (AdminController); products.is_disabled; delete blocks if users/tasks mapped |
| **Product Access** | ✓ | Grant/revoke product_members; AdminController::productMemberAdd/Remove |
| **Tasks** | ✓ | list; Manager/Super Admin: add/edit/delete on product view; delete blocks if time entries mapped |
| **User Management** | ✓ | Super Admin: add user, reset password, enable/disable, modify reporting manager; Manage Users: table (Name, Email, Role, Team, Reporting Manager, Status, Edit); Edit page for Reporting Manager, Status, Password; team filter |
| **Vertex UI** | ✓ | Login page; layout (header, sidebar, content); dashboard cards; SmartyEngine layout vars; Team Timesheet card for Manager/Lead |
| **Milestones, Costing** | ✓ | As before |
| **Time Sheet Grid** | ✓ | /timesheet/sheet; weekly grid; tasks × days; row/daily/weekly totals |
| **Reports** | ✓ | task-wise, employee-wise, performance; date range (From/To) filter; export CSV |

### Key Deliverables

- **Migrations**: products (is_disabled), product_members, milestones, tasks, time_entries (status), approvals, resource_costs, users (reporting_manager_id, is_active)
- **Models**: ProductModel, TaskModel, TimeEntryModel, ConfigModel, ConfigService, ApprovalModel, ResourceCostModel, MilestoneModel
- **Controllers**: ProductController, TaskController, TimesheetController, MilestoneController, ApprovalController, CostingController, ReportController
- **Templates**: products/list.tpl, products/view.tpl, tasks/list.tpl, timesheet/index.tpl, milestones/list.tpl, approval/pending.tpl, costing/index.tpl, reports/*
- **Routes**: /products, /products/view/(:num), POST /products/sync/(:num), /products/(:num)/tasks/add|edit|delete, /admin/products/manage|add|edit|delete|toggle-disabled|members, /timesheet, /timesheet/sheet, /timesheet/view, /timesheet/team, /timesheet/team/details, /timesheet/edit/(:num), /approval, /approval/timesheet/approve/(:num), /admin/users|add|edit/(:num)|reporting-manager|toggle-active
- **Seed**: ProductTaskSeeder, UserSeeder (Manager, Finance added)

---

### UI Design (Vertex Format)

- **FR-028**: Login page: light grey background, centered white card, "Log in to start your session", input fields with icons, Forgot Password link, dark purple Log In button
- **FR-029**: Main layout: dark purple header (logo, hamburger, notification bell, user dropdown); left sidebar (MAIN NAVIGATION); content area; footer with copyright
- **FR-030**: Dashboard: cards for Timesheet, Task, Pending Approval (with count for Manager/Lead)

**Version**: 1.6.0 | **Created**: 2026-02-19 | **Constitution**: v1.6.0 | **Updated**: 2026-02-19 (Time Sheet grid, Team Timesheet format, Reports filter, Manage Users/Products)
