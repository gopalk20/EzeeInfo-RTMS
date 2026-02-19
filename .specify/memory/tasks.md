# Tasks: RTMS Baseline

**Input**: [.specify/memory/plan.md](.specify/memory/plan.md), [.specify/memory/spec.md](.specify/memory/spec.md), [.specify/memory/analyze.md](.specify/memory/analyze.md)  
**Prerequisites**: plan.md, spec.md, constitution.md, clarify.md

**Organization**: Tasks grouped by Phase 0 (Foundation) then User Story (US1–US5). Constitution requires tests for critical paths—included. Updated per analyze.md (Iteration 1).

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: User story (US1, US2, US3, US4, US5) or Foundation

## Path Conventions

- **Backend**: `app/` (CodeIgniter 4)
- **Tests**: `tests/unit/`, `tests/Feature/` (CodeIgniter uses lowercase `unit`)
- **Templates**: `app/templates/`
- **Migrations**: Use format `YYYY-MM-DD-HHMMSS_Description.php` (e.g., `2026-02-19-000001_CreateRolesTable.php`)

---

## Phase 1: Foundation (Blocking—all user stories depend on this)

**Purpose**: Auth, roles, config, RBAC. Must complete before any user story.

**⚠️ CRITICAL**: No user story work can begin until this phase is complete.

### Migrations & Seeds

- [x] T001 [P] [Foundation] Create migration `app/Database/Migrations/2026-02-19-000001_CreateRolesTable.php` — roles (id, name: Employee, Product Lead, Manager, Finance, Super Admin)
- [x] T002 [Foundation] Create RoleSeeder `app/Database/Seeds/RoleSeeder.php` — seed 5 roles (including Super Admin)
- [x] T002a [P] [Foundation] Create migration `app/Database/Migrations/2026-02-19-000004_CreateTeamsTable.php` — teams (id, name)
- [x] T002b [Foundation] Create TeamSeeder — seed at least one default team (e.g., "Default" or org-specific)
- [x] T003 [Foundation] Create migration to add `role_id`, `username`, `first_name`, `last_name`, `phone`, `team_id` to users table; default existing users to role_id=1 (Employee); backfill first_name/last_name from `name` if present
- [x] T004 [P] [Foundation] Create migration `app/Database/Migrations/2026-02-19-000002_CreateConfigTable.php` — config (key, value) for daily_hours_limit, d_plus_n_days, working_days, standard_hours
- [x] T005 [Foundation] Create ConfigSeeder with defaults (e.g., daily_hours_limit=24, d_plus_n_days=3); run seed

### Auth & RBAC

- [x] T006 [Foundation] Create RoleFilter `app/Filters/RoleFilter.php` — check user role, deny if insufficient
- [x] T007 [Foundation] Register RoleFilter in `app/Config/Filters.php`; create filter aliases (require_manager, require_product_lead_or_manager, require_employee, require_finance_or_manager, require_super_admin)
- [x] T008 [Foundation] Extend UserModel to include role_id, team_id, username, first_name, last_name, phone; getRole(), getTeam(); ensure relationships to roles and teams
- [x] T009a [Foundation] Create AuthController `app/Controllers/AuthController.php` — login (email/password), logout; dedicated login page `app/templates/auth/login.tpl`; session handling; individual user must log in to access timesheet and all RTMS features (FR-000, FR-000a)
- [x] T009b [Foundation] Create AuthFilter `app/Filters/AuthFilter.php` — redirect unauthenticated users to login
- [x] T009c [Foundation] Protect RTMS routes (products, tasks, timesheet, approval, reports) with AuthFilter; verify CSRF on POST; preserve existing /, /users, /about as public
- [x] T010 [Foundation] Create route-role matrix `.specify/memory/route-role-matrix.md`; apply RoleFilter when adding routes in T017+
- [x] T010a [Foundation] Create ProfileController or AuthController::profile — user profile page showing Name, email, current role, team name; template `app/templates/profile/view.tpl` (FR-000b)
- [x] T010b [Foundation] Add profile link to layout/header; add logout link
- [x] T010c [Foundation] Self-service password reset: form (current password + new password) in profile or dedicated page; AuthController::resetOwnPassword; require current password; hash and save (FR-000c)
- [x] T010d [Foundation] Super Admin: UserController or AdminController — resetUserPassword(user_id, new_password); apply require_super_admin; no current password required (FR-000d)
- [x] T010e [Foundation] Super Admin: Add new user form — username, email, first_name, last_name, role (dropdown), team (dropdown), phone_number; UserController::addUser or AdminController; apply require_super_admin; create user with bcrypt password (FR-000e)
- [x] T010f [Foundation] Create `app/templates/profile/view.tpl`, `app/templates/profile/reset_password.tpl`, `app/templates/admin/add_user.tpl`, `app/templates/admin/users.tpl` (list users for Super Admin)

**Checkpoint**: Foundation ready. Auth works; logout; profile; self password reset; Super Admin add user & reset password.

---

## Phase 2: User Story 1 — Product Lead Creates Product & Onboards Team (P1) 🎯 MVP

**Goal**: Product Lead can create product, add members, set timeline/max time, link GitHub repo.  
**Independent Test**: Create product, add 2 members, set dates; verify product appears for assigned members.

### Migrations

- [x] T011 [P] [US1] Create migration `app/Database/Migrations/2026-02-19-000005_CreateProductsTable.php` — products (id, name, start_date, end_date, max_allowed_hours, github_repo_url, product_lead_id nullable, created_at, updated_at)
- [x] T012 [P] [US1] Create migration `app/Database/Migrations/2026-02-19-000006_CreateProductMembersTable.php` — product_members (product_id, user_id, role_in_product)

### Models & Controller

- [x] T013 [US1] Create ProductModel `app/Models/ProductModel.php` — getProductsForUser, getMembers, addMember, removeMember
- [x] T014 [US1] ProductModel handles membership (no separate ProductMemberModel)
- [ ] T015 [US1] Create ProductController `app/Controllers/ProductController.php` — index ✓; create, store, edit, update, addMember, removeMember (TODO)

### Templates & Routes

- [x] T016 [US1] Create Smarty template: `app/templates/products/list.tpl`
- [ ] T016 [US1] TODO: `app/templates/products/create.tpl`, `app/templates/products/edit.tpl`, `app/templates/products/members.tpl`
- [ ] T016a [US1] Create dashboard: redirect Employee to tasks/list, Product Lead/Manager to products/list after login; or `app/templates/dashboard.tpl`
- [x] T017 [US1] Add routes in `app/Config/Routes.php` for products/*; protect with AuthFilter
- [ ] T018 [US1] Link GitHub repo: form field + store github_repo_url (token storage in T024)

**Checkpoint**: US1 complete. Product Lead can create product, add members, set timeline.

---

## Phase 3: User Story 2 — Employee Executes Task & Logs Time (P1)

**Goal**: Employee views assigned tasks, links branch, logs time; status transitions via webhooks.  
**Independent Test**: Assign task; employee links branch, logs time; verify status and time recorded.

### Migrations

- [x] T019 [P] [US2] Create migration `app/Database/Migrations/2026-02-19-000007_CreateMilestonesTable.php` — milestones (id, product_id, name, due_date, release_status)
- [x] T020 [P] [US2] Create migration `app/Database/Migrations/2026-02-19-000008_CreateTasksTable.php` — tasks (id, product_id, milestone_id nullable, github_issue_id, title, status, assignee_id, linked_branch, locked, created_at, updated_at)
- [x] T021 [P] [US2] Create migration `app/Database/Migrations/2026-02-19-000009_CreateTimeEntriesTable.php` — time_entries (id, task_id, user_id, work_date, hours, is_rework, created_at)

### GitHub Integration (partial—full sync in T028+)

- [x] T022 [US2] Create GitHubService `app/Libraries/GitHubService.php` — fetch issues, branches; use PAT from env
- [x] T023 [US2] Add GitHub PAT to .env; GitHubService reads token
- [x] T024 [US2] Product sync: ProductController::syncFromGitHub — sync Issues into tasks; github_repo_url on product

### Task & Time Logging

- [x] T025 [US2] Create TaskModel `app/Models/TaskModel.php` — getByAssignee, getByProduct
- [x] T026 [US2] Create TimeEntryModel `app/Models/TimeEntryModel.php` — CRUD, getByTask, getByUser, getDailyTotal (for BR-1)
- [x] T027 [US2] Create TaskController `app/Controllers/TaskController.php` — list (assigned only) ✓; TODO: linkBranch, listBranches, create/assign for Product Lead
- [x] T028 [US2] Create TimesheetController `app/Controllers/TimesheetController.php` — log time ✓; Employee marks is_rework ✓; BR-1, BR-3 enforced ✓; TODO: edit (within D+N)
- [x] T029 [US2] Create ConfigService `app/Libraries/ConfigService.php` — getDailyHoursLimit(), getDPlusNDays(), isEditable(work_date)
- [ ] T030 [US2] Implement D+N policy in TimesheetController edit guard (FR-017) — requires edit UI
- [x] T031 [US2] Implement BR-1: validate daily total ≤ config limit before save (FR-018)
- [x] T032 [US2] Implement BR-3: block edit/log if task completed/approved (FR-015)
- [ ] T033 [US2] Reject branch link if branch already linked to another task (Edge case)
- [ ] T034 [US2] Timeline exceeded: show warning when logging time past product end_date; allow logging (FR-020a)

### Webhooks

- [ ] T035 [US2] Create WebhookController `app/Controllers/WebhookController.php` — POST endpoint for GitHub push/merge events; verify signature; update task status (In Progress on first commit, Completed on merge to main)
- [ ] T036 [US2] Register webhook route; exclude from AuthFilter in Filters.php; verify via GitHub webhook secret signature
- [ ] T037 [US2] GitHub sync failure: disable sync flag, log error, surface via flash message on Product edit or simple sync status page (FR-026)

### Templates

- [x] T038 [US2] Create `app/templates/tasks/list.tpl`, `app/templates/timesheet/index.tpl`
- [ ] T038 [US2] TODO: `app/templates/tasks/detail.tpl`, `app/templates/timesheet/edit.tpl`

**Checkpoint**: US2 complete. Employee can link branch, log time; webhooks update status.

---

## Phase 4: User Story 3 — Manager Approves Task Completion (P2)

**Goal**: Manager approves; task and time entries locked; audit trail.  
**Independent Test**: Employee completes task; Manager approves; verify locked and approval recorded.

### Approval

- [x] T039 [P] [US3] Create migration `app/Database/Migrations/2026-02-19-000010_CreateApprovalsTable.php` — approvals (id, task_id, approver_id, approved_at, status, feedback)
- [x] T040 [US3] Create ApprovalModel `app/Models/ApprovalModel.php` — create, getByTask, isApproved
- [x] T041 [US3] Create ApprovalController `app/Controllers/ApprovalController.php` — approve, reject; require_manager filter
- [x] T042 [US3] On approve: set task locked flag; record approver, timestamp (FR-022)
- [x] T043 [US3] On reject: set task status to Rework Requested; store feedback (Constitution V)
- [x] T044 [US3] RBAC: Product Lead and Employee blocked by require_manager filter (FR-001)
- [x] T045 [US3] Create `app/templates/approval/pending.tpl`

**Checkpoint**: US3 complete. Manager can approve; entries locked.

---

## Phase 5: User Story 4 — Finance Views Reports (P2)

**Goal**: Finance views task-wise, employee-wise time; rework impact; export CSV/PDF/Excel.  
**Independent Test**: Log time on tasks; Finance views reports; verify metrics visible.

### Reports

- [x] T046 [US4] Create ReportController `app/Controllers/ReportController.php` — taskWise, employeeWise, performance; require_finance_or_manager
- [x] T047 [US4] RBAC: Finance WITHOUT cost data; Manager WITH costing (FR-005)
- [x] T048 [US4] Implement Rework % = (Rework Hours / Total Hours) × 100 (BR-4)
- [x] T049 [US4] Export CSV: exportTaskWise action
- [ ] T050 [US4] Export PDF: TODO
- [ ] T051 [US4] Export Excel: TODO
- [x] T052 [US4] Create `app/templates/reports/task_wise.tpl`, `app/templates/reports/employee_wise.tpl`, `app/templates/reports/performance.tpl`, `app/templates/reports/index.tpl`

**Checkpoint**: US4 complete. Finance views and exports reports.

---

## Phase 6: User Story 5 — Employee Responds to Rework (P3)

**Goal**: Employee logs rework time; Rework % calculated.  
**Independent Test**: Task reopened; employee logs rework; verify Rework % in report.

### Rework

- [x] T053 [US5] Task status "Rework Requested" used on reject (ApprovalController)
- [ ] T053a [US5] TaskController::requestRework — set task status to Rework Requested; Product Lead + Manager only; apply require_product_lead_or_manager
- [x] T054 [US5] Timesheet log form: add checkbox "Mark as rework"; save is_rework=1 (FR-019)
- [x] T055 [US5] Rework % in reports (task-wise, employee-wise, performance)
- [ ] T056 [US5] Rework request flow: status change visible to Employee in task detail; optional notification UI

**Checkpoint**: US5 complete. Rework logging and reporting work.

---

## Phase 7: Edge Cases & Polish

**Purpose**: Member removal, max time warning, audit log, tests.

### Edge Cases

- [ ] T057a [Foundation] Define Product Lead resolution: use product_lead_id from products table, or first member with role_in_product='Product Lead'; fallback to product creator
- [ ] T057 [Foundation] Employee removed from product: reassign assigned tasks to Product Lead (Q6.3); implement in ProductController::removeMember using T057a resolution
- [ ] T058 [Foundation] Max allowed time exceeded: warn in time log form; do not block (Q2.1)
- [ ] T059 [Foundation] Create migration `app/Database/Migrations/2026-02-19-000009_CreateAuditLogTable.php`; record before/after for time entry edits (Constitution I)
- [ ] T060 [Foundation] Integrate audit logging into TimesheetController and ApprovalController

### Tests (Constitution VII)

- [ ] T061 [P] Create `tests/unit/Models/TimeEntryModelTest.php` — test daily total, D+N, BR-1
- [ ] T062 [P] Create `tests/unit/Libraries/ConfigServiceTest.php` — test D+N, daily limit
- [ ] T063 Create `tests/Feature/ApprovalTest.php` — Manager can approve; Product Lead cannot
- [ ] T064 Create `tests/Feature/TimesheetTest.php` — log time, D+N block, BR-1 block
- [ ] T065 Create `app/Database/Migrations/README.md` — document rollback order, `php spark migrate:rollback` usage

**Checkpoint**: Edge cases handled; critical path tests passing.

---

## Dependencies & Execution Order

### Phase Dependencies

| Phase | Depends On | Blocks |
|-------|------------|--------|
| Phase 1 (Foundation) | None | Phases 2–7 |
| Phase 2 (US1) | Phase 1 | Phase 3 (tasks need products) |
| Phase 3 (US2) | Phase 1, Phase 2 | Phase 4, 5, 6 |
| Phase 4 (US3) | Phase 1, 2, 3 | Phase 7 |
| Phase 5 (US4) | Phase 1, 2, 3 | Phase 7 |
| Phase 6 (US5) | Phase 1, 2, 3 | Phase 7 |
| Phase 7 (Polish) | Phases 2–6 | — |

### Recommended Order

1. **Phase 1** (Foundation) — required first
2. **Phase 2** (US1) — products before tasks
3. **Phase 3** (US2) — tasks, GitHub, time logging
4. **Phase 4** (US3) — approval
5. **Phase 5** (US4) — reports
6. **Phase 6** (US5) — rework (can overlap with Phase 5)
7. **Phase 7** — edge cases, tests

### Parallel Opportunities

- T001, T004 [P] — migrations
- T009a, T009b [P] — auth components
- T011, T012 [P] — US1 migrations
- T019, T020, T021 [P] — US2 migrations
- T039 [P] — US3 migration
- T061, T062 [P] — unit tests

---

## Implementation Strategy

### MVP First (Phases 1 + 2 + 3)

1. Complete Phase 1 (Foundation)
2. Complete Phase 2 (US1 — Products)
3. Complete Phase 3 (US2 — Tasks, Time) — minimal webhook for MVP; can use manual status initially
4. **STOP & VALIDATE**: Product Lead creates product; Employee logs time
5. Deploy/demo

### Incremental Delivery

1. Phase 1 + 2 → Products work (MVP seed)
2. Phase 3 → Tasks + time logging (core value)
3. Phase 4 → Approval (close loop)
4. Phase 5 → Reports (Finance value)
5. Phase 6 + 7 → Rework + polish

---

## Notes

- [P] = parallel-safe (different files)
- Commit after each task or logical group
- Verify migrations run: `php spark migrate`
- Run tests: `php spark test`
- Use SmartyEngine for all templates (existing `app/Libraries/SmartyEngine.php`)

---

### Phase 8: Timesheet Workflow, Reporting, Product/Task CRUD (2026-02-19)

- [x] T070 [P] Migration: users.reporting_manager_id, users.is_active; time_entries.status
- [x] T071 TimesheetController: success message on submit; status pending_approval; edit/update before approval
- [x] T072 TimesheetController: viewSummary (daily/weekly/monthly); getGroupedByProject
- [x] T073 TimeEntryModel: getPendingForApprover; approveEntry; getConsolidatedForApprover
- [x] T074 ApprovalController: Product Lead + Manager filter; approveTimesheet; timesheet pending section
- [x] T075 TimesheetController: teamView; /timesheet/team route
- [x] T076 AuthController: is_active check on login; display_name in session
- [x] T077 AuthController::profile: show reporting manager
- [x] T078 AdminController: setReportingManager; toggleActive; users.tpl (reporting manager, enable/disable)
- [x] T079 AdminController: productsManage, productAdd, productEdit, productDelete; productMemberAdd/Remove
- [x] T080 ProductController: taskAdd, taskEdit, taskDelete; require_manager; delete blocks if time entries mapped
- [x] T081 Product view: Add Task form; Edit/Delete per task (Manager/Super Admin)
- [x] T082 Vertex UI: login.tpl redesign; layout/main.tpl (header, sidebar); home.tpl dashboard cards; SmartyEngine layout vars

### Implementation Notes (2026-02-19)

- **Reporting structure**: users.reporting_manager_id; time entries routed to Manager (reports) or Product Lead (product members)
- **Timesheet workflow**: status pending_approval/approved; employee edits before approval via /timesheet/edit/(:num)
- **Product CRUD**: AdminController; delete blocks if product_members or tasks exist
- **Task CRUD**: ProductController; Manager/Super Admin only; delete blocks if time_entries exist
- **Vertex UI**: Light grey login; dark purple header/sidebar; dashboard cards (Timesheet, Task, Pending Approval)

---

**Version**: 1.5.0 | **Created**: 2026-02-19 | **Updated**: 2026-02-19 (Timesheet workflow, Reporting, Product/Task CRUD, Access control, Vertex UI)
