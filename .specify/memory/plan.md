# Implementation Plan: RTMS Baseline

**Branch**: `feature/rtms-implementation` | **Date**: 2026-02-19 | **Spec**: [.specify/memory/spec.md](.specify/memory/spec.md)  
**Input**: Baseline specification, constitution v1.9.1, clarify.md (all resolved)

---

## Summary

Build the Resource Timesheet Management System (RTMS) on the existing PHP 8.4 + CodeIgniter 4 + Smarty + MySQL stack. Core deliverables: product/task management, GitHub integration (Issues + PRs via Webhooks), role-based access (Employee, Product Lead, Manager, Finance, Super Admin), user profile (Name, email, role, team), logout, self-service password reset, Super Admin add user and reset password, time logging with D+N policy, rework tracking, Product Lead/Manager approvals, Finance reports with export. **Added (v1.9.x)**: Cloud security (HTTPS, secure cookies, rate limiting), 24h session idle (any page load refreshes), URL domain-only, Super Admin defines user cost (Manager views per-day), email reminders (employee missed timesheet: Mon–Fri fewer than 8h per work day or month end; approver consolidated weekly/monthly), configurable email templates.

**Technical approach**: Layered architecture (Controllers → Models/Services → Database). New schema via migrations. GitHub Webhooks for real-time status. RBAC via Filters. Configuration in database/env for BR-1, BR-2. Email via CodeIgniter Email (SMTP). Reminders via CLI + cron.

---

## Technical Context

| Item | Value |
|------|-------|
| **Language/Version** | PHP 8.4 |
| **Framework** | CodeIgniter 4.5.x |
| **Templating** | Smarty 5 |
| **Storage** | MySQL 8.x, UTF8MB4 |
| **Testing** | PHPUnit (CodeIgniter test runner) |
| **Target** | Web application (php spark serve / Apache) |
| **External APIs** | GitHub REST API, GitHub Webhooks; SMTP for email |
| **Constraints** | Constitution Principles I–VIII; BR-1 to BR-5 |
| **Scale** | Product-based company; multi-product, multi-member |

---

## Constitution Check

*GATE: Must pass before Phase 0. Re-check after Phase 1.*

| Principle | Status | Notes |
|-----------|--------|-------|
| I. Data Integrity & Auditability | ✓ | Approval locks; audit log for edits |
| II. Configuration Over Hard-Coding | ✓ | BR-1, BR-2, D+N, working days in config |
| III. RBAC | ✓ | Five roles (incl. Super Admin); filter-based checks |
| IV. GitHub Single Source of Truth | ✓ | Sync Issues + PRs; webhooks |
| V. Approval Workflow Integrity | ✓ | Manager-only; sequential; auditable |
| VI. Calculated Values Deterministic | ✓ | BR-4, BR-5; no override |
| VII. Testability & Traceability | ✓ | Migrations; tests for critical paths |
| VIII. Simplicity & Maintainability | ✓ | CI4 + Smarty only; no extra frameworks |

**Result**: All gates pass.

---

## Project Structure

### Documentation

```text
.specify/memory/
├── constitution.md    # Principles and rules
├── spec.md           # Baseline specification
├── clarify.md        # Clarification Q&A
├── plan.md           # This file
└── tasks.md          # (Created by /speckit.tasks)
```

### Source Code (CodeIgniter 4 layout)

```text
app/
├── Config/
│   ├── Routes.php
│   ├── Filters.php       # RBAC filters
│   └── ...
├── Controllers/
│   ├── Auth/             # Login, logout (extend existing)
│   ├── ProductController.php
│   ├── TaskController.php
│   ├── TimesheetController.php
│   ├── ApprovalController.php
│   ├── ReportController.php
│   └── WebhookController.php   # GitHub webhooks
├── Models/
│   ├── UserModel.php     # (exists)
│   ├── ProductModel.php
│   ├── TaskModel.php
│   ├── MilestoneModel.php
│   ├── TimeEntryModel.php
│   ├── ApprovalModel.php
│   └── ...
├── Libraries/
│   ├── SmartyEngine.php  # (exists)
│   ├── GitHubService.php # API + webhook handlers
│   └── ConfigService.php # BR-1, BR-2, D+N
├── Database/
│   ├── Migrations/
│   │   ├── CreateProductsTable
│   │   ├── CreateTasksTable
│   │   ├── CreateTimeEntriesTable
│   │   ├── CreateApprovalsTable
│   │   ├── CreateConfigTable
│   │   └── ...
│   └── Seeds/
│       ├── RoleSeeder.php
│       └── ConfigSeeder.php
├── Filters/
│   └── RoleFilter.php    # RBAC
└── templates/            # Smarty .tpl files
    ├── products/
    ├── tasks/
    ├── timesheet/
    ├── approval/
    └── reports/

tests/
├── Unit/
│   └── Models/
└── Feature/
    └── Controllers/

writable/
├── logs/
└── smarty_compile/
```

---

## Implementation Phases

### Phase 0: Foundation (Auth, Roles, Config)

**Goal**: Auth and roles in place; BR-1, BR-2 configurable.

| Task | Description | FR |
|------|-------------|-----|
| 0.1 | Create roles table + RoleSeeder (Employee, Product Lead, Manager, Finance) | FR-001 |
| 0.2 | Extend users table: role_id, link to roles | FR-001 |
| 0.3 | Create config table for BR-1 (daily_hours_limit), BR-2 (D, N), working_days, standard_hours | FR-018, FR-017 |
| 0.4 | Implement RoleFilter; apply to routes per role | FR-001 to FR-005 |
| 0.5 | Auth: ensure session-based, CSRF, bcrypt passwords (align with existing) | Constitution |

**Deliverable**: Users with roles; config keys; RBAC filter working.

---

### Phase 1: Products & Membership

**Goal**: Product CRUD; add/remove members; timeline, max allowed time.

| Task | Description | FR |
|------|-------------|-----|
| 1.1 | Migration: products table (name, start_date, end_date, max_allowed_hours, github_repo_url, etc.) | FR-006 |
| 1.2 | Migration: product_members (product_id, user_id, role_in_product) | FR-007 |
| 1.3 | ProductModel, ProductController; create/edit product | FR-006 |
| 1.4 | Add/remove members to product | FR-007 |
| 1.5 | Smarty templates: product list, create, edit, member management | FR-006, FR-007 |
| 1.6 | RBAC: Product Lead, Manager can manage products | FR-001 |

**Deliverable**: Product Lead can create product, add members, set timeline and max time.

---

### Phase 2: GitHub Integration & Tasks

**Goal**: Link repo; sync Issues + PRs; create tasks; assign; branch linking.

| Task | Description | FR |
|------|-------------|-----|
| 2.1 | Migration: tasks table (product_id, github_issue_id, title, status, assignee_id, linked_branch, etc.) | FR-009, FR-012 |
| 2.2 | Migration: milestones table; task-milestone optional link | FR-009 |
| 2.3 | GitHubService: OAuth/pat; fetch issues, PRs, branches | FR-026 |
| 2.4 | Product "link GitHub repo": store token, sync Issues + PRs into tasks | FR-008, FR-026 |
| 2.5 | TaskController: create task (manual or from sync), assign to employee | FR-009, FR-010 |
| 2.6 | Branch linking: list branches from GitHub API; Employee selects; one branch per task | FR-011 |
| 2.7 | GitHub Webhook endpoint: receive push/merge events; update task status | FR-013, FR-014 |
| 2.8 | Reject branch link if already linked to another task | Edge case |
| 2.9 | Sync failure: disable sync, log error, surface to admin | FR-026, Q6.1 |

**Deliverable**: Tasks from GitHub; Employee links branch; status auto-updates via webhooks.

---

### Phase 3: Time Logging & Rework

**Goal**: Log time; D+N policy; rework tagging; BR-1, BR-2, BR-4.

| Task | Description | FR |
|------|-------------|-----|
| 3.1 | Migration: time_entries (task_id, user_id, date, hours, is_rework, etc.) | FR-016 |
| 3.2 | TimesheetController: log time; Employee marks is_rework when logging | FR-016, FR-019 |
| 3.3 | D+N policy: block edit if date > work_date + N days (config) | FR-017 |
| 3.4 | BR-1: validate daily total ≤ config limit on save | FR-018 |
| 3.5 | BR-3: block edit of time entries for completed/approved tasks | FR-015 |
| 3.6 | Rework % calculation: (Rework Hours / Total Hours) × 100 | FR-019, BR-4 |
| 3.7 | Rework request: status change (e.g. "Rework Requested"); Employee notified via UI | FR-020 |

**Deliverable**: Time logging with D+N; rework tagging; BR-1, BR-2, BR-4 enforced.

---

### Phase 4: Approval Workflow

**Goal**: Manager approves; lock entries; audit trail.

| Task | Description | FR |
|------|-------------|-----|
| 4.1 | Migration: approvals table (task_id, approver_id, approved_at, status) | FR-021 |
| 4.2 | ApprovalController: Manager-only approve action | FR-021 |
| 4.3 | On approve: lock task and related time entries; record approver, timestamp | FR-022 |
| 4.4 | Rejection: return to previous state; log feedback | Constitution V |
| 4.5 | RBAC: Product Lead, Employee cannot approve | FR-001 |

**Deliverable**: Manager approves; entries locked; audit trail.

---

### Phase 5: Reports & Finance

**Goal**: Finance views reports; export CSV/PDF/Excel.

| Task | Description | FR |
|------|-------------|-----|
| 5.1 | ReportController: task-wise time, employee-wise time, rework impact | FR-023, FR-024 |
| 5.2 | RBAC: Finance, Manager can access reports; Product Lead cannot see costing | FR-005 |
| 5.3 | Export: CSV, PDF, Excel | FR-024 |
| 5.4 | Efficiency vs time spent (aggregate metrics) | FR-025 |

**Deliverable**: Finance views and exports reports.

---

### Phase 6: Edge Cases & Polish

| Task | Description |
|------|-------------|
| 6.1 | Employee removed from product: reassign tasks to Product Lead | Q6.3 |
| 6.2 | Max allowed time exceeded: warn, do not block | Q2.1 |
| 6.3 | Timeline exceeded: show warning when logging time; do not block | Q6.2 |
| 6.4 | Audit log: record before/after for editable changes | Constitution I |

---

### Phase 11: Cloud Security, Session, URL, User Cost (v1.9.0)

| Task | Description | FR |
|------|-------------|-----|
| 11.1 | HTTPS redirect, secure cookies (HttpOnly, Secure, SameSite), rate limiting on login | FR-032 |
| 11.2 | Input validation, parameterized queries, XSS prevention, security headers | FR-033 |
| 11.3 | Document security practices for voluntary disclosure | FR-034 |
| 11.4 | Session: 24h idle (86400s config); any page load refreshes | FR-000a1, Q11.1 |
| 11.5 | URL: History API replaceState; address bar shows domain only | FR-000a2, Q11.2 |
| 11.6 | User cost: Super Admin only can edit; Manager views per-day (monthly_cost / days_in_month) | FR-005c, Q11.3 |

---

### Phase 12: Email Reminders & Configurable Templates (v1.9.1)

| Task | Description | FR |
|------|-------------|-----|
| 12.1 | Email config: .env for SMTP credentials; Admin UI for from/reply-to | FR-035, Q10.1 |
| 12.2 | Migration/config: email_templates (subject, body) for 4 template types | FR-038 |
| 12.3 | Employee reminder (weekly): Mon–Fri; "missed" = any work day fewer than 8h; send to employees who missed | FR-036, Q10.2 |
| 12.4 | Employee reminder (monthly): run last day of month; validate respective month | FR-036, Q10.3 |
| 12.5 | Approver reminder: consolidated (one email per approver); list all pending timesheets | FR-037, Q10.4 |
| 12.6 | CLI command (e.g., `php spark remind:timesheet`) for cron; weekly + monthly triggers | FR-035–037, Q10.5 |
| 12.7 | Super Admin UI: manage email templates; placeholders {employee_name}, {period}, {missing_days}, {approval_count}, {pending_list}, {login_url}, {approval_url} | FR-038, Q10.6 |

---

## Data Model (High-Level)

```text
users (existing) + role_id, reporting_manager_id, is_active
roles (Employee, Product Lead, Manager, Finance, Super Admin)
config (key, value) — BR-1, BR-2, working_days, standard_hours; session_expiration (86400)
products (name, start_date, end_date, max_allowed_hours, github_repo_url, is_disabled, product_type)
product_members (product_id, user_id)
tasks (product_id, github_issue_id, title, status, assignee_id, linked_branch, milestone_id)
milestones (product_id, name, due_date, release_status)
time_entries (task_id, user_id, work_date, hours, is_rework, status, created_at)
approvals (task_id, approver_id, approved_at, status)
resource_costs (user_id, monthly_cost) — Super Admin edit only; Manager view
email_templates (type, subject, body) — employee_timesheet_reminder_weekly|monthly, approver_reminder_weekly|monthly
audit_log (entity, entity_id, user_id, action, before, after, created_at)
```

---

## Assumptions

| # | Assumption | Risk |
|---|------------|------|
| A1 | Timeline exceeded (Q6.2): Show warning only; allow logging (resolved) | Low |
| A2 | Product Lead approves timesheets; Manager approves tasks and timesheets for direct reports (Q8.3) | Low |
| A3 | Product = Project for Finance reports (Q8.2: resolved) | Low |
| A4 | GitHub Webhook requires public URL or ngrok for local dev | Low |
| A5 | Existing UserModel/Users table can be extended with role_id | Low |
| A6 | Email: .env for SMTP creds; Admin UI for non-sensitive; cron calls CLI for reminders | Low |
| A7 | Employee "missed" = any Mon–Fri work day with fewer than 8h; monthly reminder runs last day of month | Low |

---

## Dependencies

- PHP 8.4 with extensions: pdo_mysql, curl, json, mbstring
- MySQL 8.x
- GitHub App or PAT with repo scope (Issues, PRs, Webhooks)
- SMTP server or mail service for reminder emails
- Cron or system scheduler (for remind:timesheet CLI)
- Composer packages: possibly `knplabs/github-api` or similar for GitHub; export libs for PDF/Excel

---

## Complexity Tracking

No constitution violations requiring justification. Plan follows layered architecture (Controllers → Models → DB) and tech stack (PHP, CI4, Smarty, MySQL).

---

**Next step**: Run `/speckit.tasks` to generate granular tasks, or begin Phase 0 implementation.

---

## Implementation Progress (2026-02-19)

| Phase | Status | Notes |
|-------|--------|-------|
| Phase 0 (Foundation) | ✓ Complete | Auth, roles, config, RBAC |
| Phase 1 (Products) | ✓ | ProductModel, ProductController; Super Admin CRUD via AdminController |
| Phase 2 (Tasks) | ✓ | TaskModel, TaskController; Manager/Super Admin task CRUD on product view |
| Phase 3 (Time Logging) | ✓ | TimeEntryModel (status); TimesheetController; edit before approval; daily/weekly/monthly view |
| Phase 4 (Approval) | ✓ Complete | ApprovalController; task + timesheet approve/reject; POST method fix; icon buttons; Pending + Approved sections |
| Phase 5 (Reports) | ✓ Complete | ReportController; task-wise, employee-wise, performance; CSV export |
| Phase 6 (Milestones, Costing) | ✓ Complete | MilestoneController; CostingController; resource_costs |
| Phase 7 (Edge Cases) | Partial | Delete validation (user/task mapped); success/error messages |
| **Phase 8 (New)** | ✓ Complete | reporting_manager_id, is_active; user enable/disable; profile reporting manager; product access control; Vertex UI |
| **Phase 9 (Approval)** | ✓ Complete | Approval POST fix; timesheet reject; icon buttons; Approved Task/Timesheet sections |
| **Phase 10 (Leave, Dashboard, Dept)** | ✓ Complete | Leave products (product_type); Admin Dashboard (Manager+Super Admin); Team Timesheet department filter; Manage Users form fix |
| **Phase 11 (Security, Session, URL, Cost)** | ✓ Partial | Cloud security (CSRF, SecureHeaders, rate limit); 24h session idle; user cost in Manage Users > Edit; Manager per-day view in Team Timesheet; SECURITY.md; T114 (URL masking) TODO |
| **Phase 12 (Email Reminders)** | Pending | SMTP config; employee reminder (Mon–Fri fewer than 8h, month end); approver consolidated; CLI + cron; configurable templates |

---

**Version**: 1.9.1 | **Created**: 2026-02-19 | **Updated**: 2026-02-20 (Phase 11 partial; costing redesign; security; user cost in Manage Users)
