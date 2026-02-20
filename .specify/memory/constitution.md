<!-- ================================================================================
SYNC IMPACT REPORT - Constitution v1.9.1
================================================================================
Date: 2026-02-19
Version Change: v1.9.0 → v1.9.1 (Email reminders, configurable templates)
Ratification Date: 2026-02-19

CHANGES FROM v1.9.0:
✓ Email configuration - System MUST support configurable email (SMTP etc.) for sending reminder emails
✓ Employee timesheet reminder - Send email to employees who missed timesheet: weekly (Mon–Fri; missed = any work day fewer than 8h); monthly runs last day of month
✓ Approver reminder - Send email to approvers every week or month to approve respective pending timesheets
✓ Configurable email templates - Super Admin can modify email subject and content for all reminder templates; stored in config or email_templates table

CHANGES FROM v1.8.1:
✓ Cloud Security - New section: Security for Cloud Deployment; HTTPS, secure cookies, rate limiting, input validation, SQL injection prevention, XSS prevention, security headers; voluntary disclosure best practices
✓ Session Idle - Session remains valid for 24 hours of idle time; configurable session expiration (config); logout only after 24h idle
✓ Browser URL - Address bar should display only domain (or base URL); avoid exposing internal routes/paths in URL for security (History API replaceState or equivalent)
✓ User Cost (Salary) - Super Admin defines monthly cost (salary) per user; stored in resource_costs; Manager sees per-day cost spent per employee; per-day = monthly_cost / days_in_month (Jan=31, Apr=30, Feb=28/29)

CHANGES FROM v1.8.0:
✓ Time Sheet Grid - Removed from UI navigation: no sidebar link, no button on View Summary or index; route /timesheet/sheet remains available if accessed directly
✓ Smarty template fix - Replaced invalid ucfirst modifier with capitalize in sheet.tpl
✓ Date selection - Form action uses site_url() for correct submission; selected date preserved (form_action passed to view.tpl, sheet.tpl)
✓ Grid empty state - When period has no time entries, grid shows "No time entries for this period" instead of empty task rows
✓ Monthly period link - Preserves month_value when switching to monthly view

CHANGES FROM v1.7.0:
✓ Leave products - products.product_type; LeaveProductsSeeder (Holiday, Sick Leave, Planned Leave, Training); assignee_id=null for leave tasks; TaskModel::getByAssignee includes leave products for all users; TimesheetController::log allows leave tasks (no assignee requirement)
✓ Admin Dashboard - /admin/dashboard for Manager + Super Admin; Overall Hours (billable vs non-billable), Work Hours Summary (monthly bars), Resource Allocation (by project incl. leave), Pending Approvers, Financial Summary
✓ Team Timesheet department filter - Department dropdown filters by team; filter preserved across Daily/Weekly/Monthly, View, and Back links
✓ Manage Users form fix - Removed duplicate team hidden input; team filter form preserves search/sort/dir; Department/Team label

CHANGES FROM v1.6.0:
✓ Approval POST method fix - CodeIgniter getMethod() returns 'POST' (uppercase); fixed strtoupper check in ApprovalController (approveTimesheet, approve, reject)
✓ Timesheet reject - ApprovalController::rejectTimesheet; TimeEntryModel::rejectEntry (status=rejected); POST /approval/timesheet/reject/(:num)
✓ Approval page layout - Pending sections (tasks, timesheet) with Approve/Reject; Approved sections (Approved Task Completions, Approved Timesheet Entries) with values in respective columns
✓ Approval UI - Small icon buttons: green checkmark for approve, red X for reject; TimeEntryModel::getApprovedForApprover

CHANGES FROM v1.5.0:
✓ Time Sheet grid - /timesheet/sheet; tasks as rows, days as columns; weekly view with hours per task per day; row totals, daily totals; budget progress (used/max h)
✓ Team Timesheet format - resource allocation table: Employee Name, Department, Allocation, Billing Role, Billing Rate, Hours Spent/Allocated (progress bar), Actions; one row per team member; date pickers (daily/weekly/monthly); View link to member details
✓ Reports date filter - From/To date selection on Reports page; task-wise, employee-wise, performance, export all filter by selected date range
✓ Manage Users redesign - table: Name, Email, Role, Team, Reporting Manager, Status, Edit; Edit page for Reporting Manager, Status, Password; team filter
✓ Manage Products - Disable/Enable toggle (products.is_disabled); Status column; Remove button; disabled products excluded from main product list
✓ Timesheet flow - redirect after log to daily summary; success message; Time Entry Details table prominent
✓ Team Timesheet visibility - Dashboard card for Manager/Super Admin; /timesheet/team/details for member entries

CHANGES FROM v1.4.0:
✓ Timesheet workflow - status (pending_approval, approved); success message on submit; employee can edit before approval
✓ Timesheet views - daily/weekly/monthly; Project Name | Time | Total; separate module
✓ Product Lead approvals - Product Lead can approve timesheets for their product members
✓ Reporting structure - users.reporting_manager_id; time entries routed to mapped lead/manager
✓ Consolidated view - Lead/Manager sees team timesheet by period
✓ Profile - displays reporting manager
✓ Super Admin product CRUD - add, edit, delete, rename product; delete blocks if users/tasks mapped
✓ Manager/Super Admin task CRUD - create, edit, delete task; delete blocks if time entries mapped
✓ Super Admin access control - grant/revoke product access to manager/product lead (product_members UI)
✓ User enable/disable - users.is_active; blocked login when disabled
✓ Super Admin - modify reporting person (reporting_manager_id) for any user
✓ UI design - Vertex format: login page, dashboard with header/sidebar, dashboard cards

CHANGES FROM v1.3.0:
✓ Costing - ResourceCostModel, CostingController; resource_costs; BR-5; Manager-only
✓ Approval - ApprovalModel, ApprovalController; approve/reject; lock task
✓ Reports - ReportController; task-wise, employee-wise, performance; CSV export
✓ GitHub - GitHubService; fetch issues; Product syncFromGitHub (GITHUB_PAT)
✓ Milestones - MilestoneModel, MilestoneController; milestones/list.tpl

CHANGES FROM v1.2.0:
✓ Data Models - Product, ProductMember, Milestone, Task, TimeEntry, Approval, ResourceCost
✓ Time Logging - TimesheetController; log time; BR-1 (daily limit); is_rework flag
✓ Products - List, view, sync from GitHub; ProductModel, ProductController
✓ Tasks - List assigned tasks; TaskModel, TaskController
✓ ConfigService - getDailyHoursLimit(), getDPlusNDays(), isEditable(), getWorkingDays(), getStandardHours(), calculateHourlyCost()

CHANGES FROM v1.1.0:
✓ Supported Roles - Added Super Admin (5th role); add users, reset any user's password
✓ Data Models - User (username, first_name, last_name, phone, team_id); Team entity
✓ Security - Logout, user profile, self password reset, Super Admin capabilities

CHANGES FROM v1.0.0:
✓ Supported Roles - Product Lead CANNOT approve (only Manager); Finance CANNOT see/manage costing (only Manager)
✓ Principle IV - GitHub sync: Issues + PRs; Webhooks for status; one branch per task; Employee selects from list
✓ Principle V - Only Manager can approve; Product Lead explicitly cannot
✓ Definitions - Added Product, Task status flow, Rework tagging (Employee marks is_rework)
✓ Edge Cases - New section: branch reuse, sync failure, timeline exceeded, member removal, max time
✓ Data Models - Aligned with spec (Product, TimeEntry with is_rework, etc.)
✓ Integration - Issues + PRs; Webhooks; disable sync on failure

ALIGNMENT SOURCES: spec.md, clarify.md, plan.md, tasks.md

COMMIT MESSAGE: docs: update constitution v1.1.0 (align with spec, clarify, plan)
================================================================================ -->

# Resource Timesheet Management System (RTMS) Constitution
<!-- Spec Kit Constitution for the RTMS project -->

## Purpose & Scope

**Purpose**: Establish the authoritative principles, rules, and standards governing the Resource Timesheet Management System—a web-based, centralized platform for product-based companies to manage employee timesheets, costing, milestone tracking, GitHub integration, approval workflows, performance tracking, and reporting.

**Scope**: All development, configuration, and operational decisions for RTMS must align with this constitution. External integrations (GitHub, HR systems) must adhere to defined contracts.

**Definitions**:
- **D+N Policy**: Timesheet entries are editable only for N calendar days after the work date. Configurable per organization (config keys for D and N).
- **Rework**: Additional effort spent due to task reopening, defect fixes, or requirement corrections. Employee explicitly marks time entry as rework when logging. Measured for quality and cost impact.
- **Milestone**: A time-bounded deliverable tied to one or more tasks; optional relationship—tasks and milestones can exist independently.
- **Product**: A deliverable or project with name, timeline (start/end date), maximum allowed time, GitHub repo link, and members.
- **Task Status Flow**: To Do → (first commit to linked branch) → In Progress → (branch merged to main) → Completed. Transitions detected via GitHub Webhooks.

## Core Principles

### I. Data Integrity & Auditability (NON-NEGOTIABLE)

All timesheet and approval data is immutable after final approval. Every change to editable records must be logged with: user, timestamp, before/after state. Locked (final-approved) entries cannot be modified—corrections require formal reversal workflow. Financial and performance reports must be traceable to source records.

### II. Configuration Over Hard-Coding

Business rules must be configurable where specified: daily hours limit (BR-1), D+N edit window (BR-2), standard hours, working days. Configuration lives in database or `.env`—never in application code. Defaults exist for development; production overrides are explicit.

### III. Role-Based Access Control (RBAC)

Exactly five roles: **Employee**, **Product Lead**, **Manager (Admin)**, **Finance**, **Super Admin**. Every feature enforces role checks. No implicit elevation; no shared credentials. Each role has a minimum-necessary permission set defined in the access matrix.

### IV. GitHub as Single Source of Truth for Tasks

Task metadata (issue ID, title, status, labels) originates from GitHub. Sync scope: Issues + Pull Requests. Synchronization is unidirectional (GitHub → RTMS) via GitHub Webhooks for real-time status (first commit → In Progress; merge to main → Completed). Employee links branch by selecting from GitHub API–listed branches; one branch per task—no reuse across tasks. Sync failures: disable sync until fixed; log and surface error to admin.

### V. Approval Workflow Integrity

Only **Manager** can approve task completion. Product Lead and Employee cannot approve. Multi-level approval is sequential and stateful. No skip-level approvals without explicit override (audit required). Each approval level records approver, timestamp, and decision. Rejection returns to previous state with feedback; resubmission creates a new approval chain entry.

### VI. Calculated Values Are Deterministic

Rework %, hourly cost, performance scores are computed from stored inputs using documented formulas. No manual override of calculations; fix inputs instead. Formulas:
- **BR-4**: Rework % = (Rework Hours / Total Hours) × 100
- **BR-5**: Hourly Cost = Monthly Cost / (Working Days × Standard Hours)

### VII. Testability & Traceability

Critical paths (timesheet validation, approval workflow, costing) require automated tests. Database migrations are versioned; rollback paths documented. Feature work links to functional requirements for traceability.

### VIII. Simplicity & Maintainability

PHP 8.4 + CodeIgniter 4 + Smarty + MySQL. No framework sprawl. Business logic in Models/Controllers; presentation in Smarty templates. Prefer CodeIgniter conventions over custom abstractions.

## Data Models

The RTMS operates on core entities, each with defined responsibilities:

- **User**: username, email, first_name, last_name, employee_id, phone, password (bcrypt/argon2-hashed), role_id, team_id, reporting_manager_id, is_active; profile shows Name, email, employee ID, current role, team name, reporting manager; profile editable (first name, last name, email, employee_id) by logged-in user
- **Team**: id, name; users belong to a team
- **Product**: Sourced from GitHub repository (Super Admin adds repo details); Name, timeline, max allowed time, GitHub repo link, team_id (mapped team; only team members can bill), members, is_disabled, product_type (null=normal, 'leave'=leave); optional link to Project; disabled products excluded from main product list; leave products (Holiday, Sick Leave, Planned Leave, Training) available for all users
- **Task**: Work units from GitHub sync (Issues synced and displayed under each product); status To Do/In Progress/Completed; assignee; linked branch (one per task); optional milestone link
- **Milestone**: Time-bounded deliverable; optional link to tasks; release status
- **TimeEntry**: Logged time against a task; work_date, hours, is_rework flag, status (pending_approval, approved); subject to D+N policy and approval workflow; employee can edit while pending_approval
- **Approval**: Task approval; Manager-only; records approver, timestamp, status; locks task and time entries
- **CostConfig**: Resource costing (Manager-only visibility); monthly cost, working days, standard hours
- **Config**: Key-value for BR-1 (daily_hours_limit), BR-2 (D, N), working_days, standard_hours; email_templates (subject, body for employee timesheet reminder, approver reminder)
- **AuditLog**: Before/after for editable changes; user, timestamp, entity, action

These entities form the persistent domain model; all business logic operates on these constructs. No new core entities may be introduced without formal architectural review.

## Business Rules (Mandatory)

| ID | Rule | Enforcement |
|----|------|-------------|
| BR-1 | Timesheet cannot exceed 24 hours per day (configurable limit) | Validation on save; config key for limit |
| BR-2 | Timesheet editable only within D+N days from work date | Edit guard; config key for D and N |
| BR-3 | Final approved entries are locked; no modification | DB constraint + application guard |
| BR-4 | Rework % = (Rework Hours / Total Hours) × 100 | Calculated; displayed; not stored as override |
| BR-5 | Hourly Cost = Monthly Cost / (Working Days × Standard Hours) | Used for costing; config for Working Days, Standard Hours |

## Supported Roles

The RTMS enforces four roles with distinct capabilities (aligned with spec.md):

- **Employee**: Can view assigned products/tasks, change task status, link GitHub branch (select from list), log time (with is_rework option), respond to rework requests. Cannot create products/tasks, assign tasks, approve, view other employees' performance, or edit completed/approved timing.
- **Product Lead**: Can add/remove product members, integrate GitHub repo, assign tasks, monitor progress. Can approve timesheet entries for members of products they lead. Cannot see financial costing or modify system-wide users/roles. Sees consolidated timesheet for their product members.
- **Manager (Admin)**: All Product Lead capabilities plus: approve task completion and timesheet entries (for direct reports via reporting_manager_id), system-wide user/role management, view and manage financial costing, create/delete/modify tasks. Sees consolidated timesheet for their reports. **Per-day cost**: Manager sees per-day cost spent per employee (monthly_cost / days_in_month, e.g. Jan=31, Apr=30).
- **Finance**: Can view employee-wise and task-wise time consumption, rework impact, performance reports; export CSV/PDF/Excel. Cannot create/modify products or tasks, approve or change task status, or see financial costing (Manager only).
- **Super Admin**: Full system access; add new users; reset any user's password; enable/disable users (disabled users cannot log in); modify reporting manager for any user; product CRUD (add, edit, delete, rename); grant/revoke product access to manager/product lead; Manager and Super Admin share task CRUD (create, edit, delete). Product/Task delete: block if users or time entries mapped; show success/error messages for CRUD operations. **Define user cost (salary)**: Super Admin can set monthly cost per user (stored in resource_costs); used for per-day cost calculation visible to Manager. **Email templates**: Super Admin can configure email reminder templates (subject and content) for employee timesheet reminders and approver reminders.

RBAC rules are evaluated at the filter/controller level and cannot be bypassed.

## Technology Stack

| Layer | Technology | Constraint |
|-------|------------|------------|
| Backend | PHP 8.4 | Use typed properties, strict types where applicable |
| Framework | CodeIgniter 4 | Follow PSR-4; use built-in validation, filters, migrations |
| Templating | Smarty 5 | Logic-free templates; assign only; no raw SQL in templates |
| Database | MySQL 8.x | UTF8MB4; transactions for multi-step operations |
| External API | GitHub REST/GraphQL | Rate limiting, token rotation, error handling |

**Integration Points**: GitHub (Issues + PRs, Webhooks for push/merge); Email (Gmail SMTP) for approval/rejection and reminder notifications; future: HR/payroll systems via defined APIs.

**Email & Reminders**: Configurable email (SMTP); .env for credentials, Admin UI for non-sensitive. Employee reminder: weekly (Mon–Fri; missed = any work day fewer than 8h) or monthly (last day of month). Approver reminder: consolidated, weekly/monthly. Cron/CLI triggers automatically. Super Admin configures templates (placeholders: employee_name, period, missing_days, approval_count, etc.). Only Super Admin edits user cost; Manager views per-day.

## Security & Compliance

### Authentication & Session

- **Authentication**: Login page required. Logout available. User profile (Name, email, role, team name). Self-service password reset. Super Admin can reset any user's password and add new users. Individual users authenticate via email/password before accessing any RTMS feature. Session-based; secure cookie flags; CSRF on all state-changing requests
- **Session Idle**: Session MUST remain valid for 24 hours of idle time before automatic logout. Configurable via config (session expiration in seconds; default 86400 for 24h). User activity refreshes the session; inactivity for 24h triggers logout.
- **Authorization**: Filter-based role checks on every controller; no client-side-only enforcement
- **Sensitive Data**: Passwords hashed (bcrypt/argon2); GitHub tokens encrypted in DB or env; no secrets in logs
- **Audit Trail**: Approval actions, role changes, config changes, time entry edits (before/after) logged with user and timestamp
- **Data Retention**: Define retention for timesheet data; comply with local labor regulations

### Security for Cloud Deployment (Anti-Hacking)

- **HTTPS**: All production traffic MUST use HTTPS (TLS 1.2+). Enforce secure redirect; no mixed content.
- **Secure Cookies**: Session and auth cookies MUST have HttpOnly, Secure, SameSite (Strict or Lax) flags when deployed to cloud.
- **Rate Limiting**: Implement rate limiting on login, password reset, and sensitive endpoints to prevent brute-force and DoS.
- **Input Validation**: All user input validated server-side; reject malformed or oversize payloads; use parameterized queries (no raw SQL concatenation).
- **SQL Injection Prevention**: Use query builder or prepared statements only; never concatenate user input into SQL.
- **XSS Prevention**: Escape all output; use Smarty |escape; no raw HTML from user input; Content-Security-Policy headers where feasible.
- **Security Headers**: Set X-Content-Type-Options: nosniff, X-Frame-Options: DENY or SAMEORIGIN, Referrer-Policy as appropriate.
- **Browser URL**: The application SHOULD NOT expose internal routes or page paths in the browser address bar. Only domain (or base URL) should be displayed where feasible—e.g., via History API replaceState, hash-based routing, or equivalent—to reduce information disclosure to attackers.
- **Voluntary Disclosures**: Document security practices (e.g., in a security policy or README): data encryption at rest/transit, access controls, audit logging, vulnerability reporting contact.

## Edge Cases (Resolved)

| Scenario | Behavior |
|----------|----------|
| Branch already linked to another task | Reject; one branch per task |
| GitHub sync failure (rate limit, token expired) | Disable sync until fixed; surface error to admin |
| Timeline (End Date) exceeded; tasks incomplete | Show warning when logging time; allow logging (no block) |
| Employee removed from product | Reassign assigned tasks to Product Lead |
| Maximum allowed time exceeded | Warn; do not block new time logs |

## Governance

This constitution is the source of truth for all development decisions on the RTMS project. All feature specifications, plans, and implementations MUST align with these eight core principles, five business rules, and supporting sections.

**Amendment Process**: Amendments require:
1. Written justification documenting why existing principles are insufficient
2. Explicit consent from project stakeholders
3. Updated version number (semantic versioning: MAJOR for principle removals/redefinitions, MINOR for additions, PATCH for clarifications)
4. A dated entry in this document
5. Propagation to all affected templates and documentation

**Compliance Review**: Before each implementation phase, verify adherence to:
- Data integrity and auditability are maintained
- Configuration over hard-coding is followed
- RBAC is enforced (only Manager approves; only Manager sees costing)
- GitHub sync: Issues + PRs; Webhooks; one branch per task; Employee selects from list
- Approval workflow integrity is preserved (Manager-only)
- Calculated values use documented formulas (BR-4, BR-5)
- Technology stack constraints are respected
- Business rules BR-1 through BR-5 are enforced
- Edge cases handled per table above

**Version**: 1.9.1 | **Ratified**: 2026-02-19 | **Last Amended**: 2026-02-19 (Email reminders, configurable templates)
