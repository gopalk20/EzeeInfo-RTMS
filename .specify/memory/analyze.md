# Analysis Report: RTMS Implementation

**Date**: 2026-02-20  
**Scope**: Spec–code alignment, Phase 11–12–13 readiness, gaps, recommendations  
**Input**: tasks.md, spec.md, plan.md, constitution.md, clarify.md, checklist.md, existing codebase  
**Versions**: Spec v1.9.2 | Plan v1.9.2 | Constitution v1.9.1

---

## Executive Summary (Iteration 3)

| Category | Status | Action |
|----------|--------|--------|
| Phase 11 (Security, Cost) | ✓ Mostly complete | T114 (URL masking) pending; Costing RBAC ✓ (no save route) |
| Phase 12 (Email) | Not started | T120–T125: SMTP config, templates, CLI, reminders |
| Phase 13 (v1.9.2) | Partial | T130, T133, T134, T136 done; T131, T132, T135 pending |
| Spec–code alignment | Minor drift | Profile view-only (no edit); no products.team_id; timesheet Task-only (no Product/Task option) |
| Critical path | Phase 12 email; Phase 13 T130–T136 | Foundation for approval emails; core Phase 13 features |

---

## 1. Tasks: Actionability & Completeness

### 1.1 Tasks Requiring Clarification or Split

| Task | Issue | Recommendation |
|------|-------|----------------|
| **T009** | "Verify auth" is vague. No login controller exists. Auth not implemented. | Split: T009a Add LoginController, login form, session; T009b Add logout; T009c Ensure CSRF on forms; T009d Verify bcrypt (already in UserModel) |
| **T010** | "Create route-role matrix document; apply filters to placeholder routes" | "Placeholder routes" undefined. New RTMS routes don't exist yet. Defer filter application to when routes are added (T017, etc.). Create route-role matrix as `.specify/memory/route-role-matrix.md` |
| **T018** | "Token storage in T030" — T030 is D+N policy. Token storage is in T024. | Fix reference: T018 stores github_repo_url; T024 handles token (encrypted) |
| **T024** | "Product link repo: store token (encrypted)" — encryption mechanism not specified | Add subtask: use CodeIgniter Encryption or base64+env key; document in plan |
| **T037** | "Surface in admin UI" — admin UI not defined | Add: Create simple admin/config view for sync status, or log + flash message on next Product edit |
| **T047** | Contradicts FR-005. "Finance can" access costing — but FR-005 says Finance CANNOT see costing. | Fix: Finance accesses reports WITHOUT cost data (time, rework %); Manager accesses full reports WITH costing. Revise T047 description. |
| **T053** | "Product Lead or Manager can set" — need controller action | Add: TaskController::setReworkRequested or status update action; Product Lead + Manager only |
| **T057** | "Reassign to Product Lead" — which one? Product may have multiple members. | Clarify: Reassign to product's Product Lead (role_in_product or first Manager/Product Lead in product_members). If none, reassign to product creator/owner. |

### 1.2 Tasks with Missing Dependencies

| Task | Missing Dependency |
|------|--------------------|
| **T017** | Depends on T006, T007 (RoleFilter) being complete. Apply filters when adding routes. |
| **T036** | Webhook route must be public; document that Filters.php excludes webhook path from auth |
| **T054** | Depends on T028 (TimesheetController). "Mark as rework" is part of log form — can merge into T028 or keep as separate UI task. |

### 1.3 Tasks Needing Concrete File Paths

| Task | Current | Recommended |
|------|---------|-------------|
| **T001, T004, etc.** | `xxxx_CreateRolesTable` | Use CodeIgniter format: `2026-02-19-000001_CreateRolesTable` (date-HHMMSS-number) |
| **T061** | `tests/Unit/Models/TimeEntryModelTest.php` | CodeIgniter uses `tests/unit/` (lowercase). Verify path: `tests/unit/Models/TimeEntryModelTest.php` |
| **T062** | `tests/Unit/Libraries/ConfigServiceTest.php` | Same: `tests/unit/Libraries/ConfigServiceTest.php` |

### 1.4 Duplicate or Overlapping Tasks

| Tasks | Overlap | Action |
|-------|---------|--------|
| T030, T031, T032 | All in TimesheetController; T028 says "enforce BR-1, BR-2, T030, T031, T032 | T028 is the main implementation; T030–T032 are guard/validation specifics. Keep as sub-items of T028 or merge into single "TimesheetController with all guards" task. |
| T054, T028 | T054 "add checkbox is_rework" could be part of T028 | Keep T054 as explicit; T028 implements backend, T054 ensures UI has the checkbox. |

---

## 2. Missing Deliverables

### 2.1 Authentication Flow (Critical)

The codebase has **no authentication**. Existing routes `/`, `/users`, `/about` are public. Tasks assume "session-based auth" but none exists.

| Missing | Description |
|---------|-------------|
| **LoginController** | Login form, credential check, session start |
| **Logout** | Session destroy |
| **Auth filter** | Redirect unauthenticated users to login for protected routes |
| **Login template** | `app/templates/auth/login.tpl` |

**Recommendation**: Add to Phase 1 (Foundation):
- T009a: Create AuthController with login, logout
- T009b: Create AuthFilter (redirect if no session)
- T009c: Create login.tpl; protect products, tasks, timesheet, approval, reports routes

### 2.2 Employee Dashboard

Spec US1: "I see the product and its tasks in my dashboard." No task creates a dashboard.

| Missing | Description |
|---------|-------------|
| **Dashboard route** | Employee/Product Lead home after login |
| **Dashboard template** | Shows assigned products, tasks, quick actions |

**Recommendation**: Add T016a or extend T016: Create `app/templates/dashboard.tpl`; DashboardController or redirect Employee to tasks/list, Product Lead to products/list.

### 2.3 Rework Request Action

T053 adds status "Rework Requested" but no task implements who sets it. T056 says "status change visible" — need controller action.

| Missing | Description |
|---------|-------------|
| **Set rework status** | TaskController::requestRework or updateStatus; Product Lead + Manager only |
| **Task status enum** | To Do, In Progress, Completed, Rework Requested (and optionally Rejected) |

**Recommendation**: Add T053a: TaskController action to set status to Rework Requested; apply require_product_lead_or_manager.

### 2.4 Product Lead for Reassignment (T057)

When removing employee, "reassign to Product Lead" — need rule for which user.

| Missing | Description |
|---------|-------------|
| **Product Lead resolution** | Product has members; which member is Product Lead? `role_in_product` in product_members, or separate product_lead_id? |

**Recommendation**: Add `product_lead_id` to products table (or derive from product_members where role_in_product='Product Lead'). If multiple, use first. If none, use product creator (created_by field).

### 2.5 Migration Rollback Documentation (T065)

T065 says "Document migration rollback" — migrations have `down()` but no central doc.

**Recommendation**: Add `app/Database/Migrations/README.md` with rollback order (reverse of up) and `php spark migrate:rollback` usage.

---

## 3. Edge Cases Not Covered

| Edge Case | Current Coverage | Gap |
|-----------|------------------|-----|
| **Existing users without role_id** | Migration adds role_id (nullable). T003. | UserModel must handle null role_id; RoleFilter must redirect or deny. Add default role for existing users in migration (e.g., Employee) or require role assignment on first login. |
| **Product with no Product Lead** | Not specified | T057 reassignment fails. Define: product creator as fallback, or block member removal until Product Lead assigned. |
| **Task with no assignee** | Tasks can have assignee_id nullable? | Clarify: Task must be assigned before Employee can work. Reject branch link if assignee_id null. |
| **Rework % when Total Hours = 0** | BR-4: (Rework / Total) × 100 | Division by zero. Define: return 0 or N/A when Total = 0. |
| **Concurrent approval** | Two Managers approve same task | Add DB unique constraint or check: only one approval per task (or approval chain). |
| **Webhook replay attack** | Verify GitHub signature | T035 says "verify signature" — ensure implemented; document secret config. |

---

## 4. Backward Compatibility with Existing APIs

### 4.1 Existing Routes & Behavior

| Route | Controller | Behavior | Compatibility |
|-------|------------|----------|---------------|
| `GET /` | Home::index | RTMS landing page | **Preserve** — no change |
| `GET /users` | Home::users | List all users (no auth) | **Preserve** — consider: after auth, restrict to Manager? Or keep public for demo. Recommend: keep as-is for backward compat; add auth later as optional. |
| `GET /about` | Home::about | Version info | **Preserve** — no change |

### 4.2 Existing Data & Schema

| Table/Model | Current Schema | Change | Compatibility |
|-------------|----------------|--------|---------------|
| **users** | id, name, email, password, created_at, updated_at | Add role_id (nullable, then backfill) | **Compatible** — migration adds column; existing rows get default role or NULL. UserModel::allowedFields must add role_id. |
| **UserModel** | allowedFields: name, email, password | Add role_id | **Compatible** — extend allowedFields; add getRole() joins roles table. |
| **Home controller** | No auth | No change | **Compatible** — Home stays as-is. New RTMS routes are additive. |

### 4.3 Route Additions (Additive)

All new routes are **additive** — no existing routes modified or removed:

- `products/*`, `tasks/*`, `timesheet/*`, `approval/*`, `reports/*`, `webhook/*`, `auth/login`, `auth/logout`

**Compatibility**: ✓ No breaking changes to existing routes.

### 4.4 Recommendations

1. **Preserve** `/`, `/users`, `/about` — do not change.
2. **Extend** users table via migration (role_id); ensure migration is reversible.
3. **UserModel** — add `role_id` to allowedFields; add `getRole()`; handle existing users (migration default role_id=1 for Employee or similar).
4. **Auth** — new routes require auth; Home routes can remain public or be optionally protected later.

---

## 5. Summary of Required Changes

### High Priority

1. **Add Auth tasks** (T009 split): LoginController, AuthFilter, login template, protect RTMS routes.
2. **Fix T047**: Finance cannot see costing; Manager can. Reports for Finance = time + rework only.
3. **Add dashboard** for Employee/Product Lead (or document redirect to tasks/products).
4. **Clarify T057**: How to resolve "Product Lead" for reassignment — schema or rule.

### Medium Priority

5. **Add T053a**: Controller action for setting "Rework Requested" status.
6. **Fix T018/T024** reference (token in T024, not T030).
7. **Rework % edge case**: Define behavior when Total Hours = 0.

### Low Priority

8. **Migration timestamp format**: Use `YYYY-MM-DD-HHMMSS` in task descriptions.
9. **T065**: Create Migrations README with rollback order.
10. **Webhook security**: Document GitHub webhook secret configuration.

---

## 6. Updated Tasks to Add

| New Task | Phase | Description |
|----------|-------|-------------|
| T009a | Foundation | Create AuthController (login, logout); login.tpl; session handling |
| T009b | Foundation | Create AuthFilter; redirect unauthenticated to login |
| T009c | Foundation | Protect RTMS routes (products, tasks, timesheet, approval, reports) with AuthFilter |
| T016a | US1 | Create dashboard view or redirect: Employee → tasks, Product Lead → products |
| T053a | US5 | TaskController::requestRework (set status); Product Lead + Manager only |
| T057a | Phase 7 | Define Product Lead resolution: product_lead_id or role_in_product; implement in removeMember |

---

## 7. Implementation Status (2026-02-20)

**Implemented**:
- Auth, login, logout, profile (view-only), password reset; is_active blocks disabled users
- Timesheet: status (pending_approval, approved); success message; edit before approval; daily/weekly/monthly view; task dropdown for log
- Reporting: users.reporting_manager_id; time entries routed to Manager/Product Lead; profile shows reporting manager
- Approval: Product Lead + Manager + Super Admin; task completion + timesheet approvals (no email on approve/reject)
- Team timesheet: /timesheet/team; consolidated view; per-day cost for Manager
- Products: list, view, syncFromGitHub; Super Admin CRUD; grant/revoke product_members; product_type (leave)
- Tasks: list; Manager/Super Admin add/edit/delete; delete blocks if time entries mapped
- User management: Super Admin add user, reset password, enable/disable, modify reporting manager, monthly cost (Manage Users > Edit)
- Vertex UI: login page, layout (header, sidebar), dashboard cards; home.tpl; admin/dashboard.tpl
- Costing: display only (user + project); no save route
- Cloud security: CSRF, SecureHeaders, rate limit, SECURITY.md; session 24h

**Implemented (Phase 13)**: T130 profile edit, T133 product–team mapping, T134 timesheet Product/Task flow, T136 unified dashboard. **TODO**: Phase 12 (email); T131 (Add from GitHub), T132 (Issues sync), T135 (SMTP approval emails); T053a, T057, T114, Webhooks, PDF/Excel

---

## 8. Iteration 2: Status of Iteration 1 Recommendations (2026-02-19)

| Rec. | Status | Notes |
|------|--------|------|
| T009 split (Auth) | ✓ Done | T009a–T009c implemented; AuthController, AuthFilter, login.tpl |
| T047 (Finance costing) | ✓ Done | ReportController: Finance sees time only; Manager sees costing |
| Dashboard (T016a) | ✓ Done | Vertex UI dashboard cards; redirect by role |
| T053a (requestRework) | ❌ Not done | No TaskController::requestRework; status set only on ApprovalController reject |
| T057 (reassign on remove) | ❌ Not done | ProductModel::removeMember only deletes product_members; does not reassign tasks |
| T057a (Product Lead resolution) | ✓ Schema done | `products.product_lead_id` exists and used; resolution rule: use product_lead_id |
| T065 (Migration README) | ❌ Not done | No `app/Database/Migrations/README.md` |
| Rework % when Total=0 | ⚠ Unknown | Check ReportController/TimeEntryModel for division-by-zero guard |

---

## 9. Iteration 2: New Gaps & Phase 11–12 Readiness

### 9.1 Spec–Code Drift

| Item | Spec/FR | Current Code | Gap |
|------|---------|--------------|-----|
| **User cost edit** | FR-005c, Q11.3: Only Super Admin can edit | ✓ Done: AdminController::userEdit (Manage Users > Edit); no costing/save route | — |
| **Session expiration** | FR-000a1: 24h idle (86400s) | ✓ Done: Session.php 86400; config table session_expiration | — |
| **Costing** | User vs project costing | ✓ Done: Costing page shows user costing + project costing; user cost in Manage Users | — |

### 9.2 Phase 11–12 Prerequisite Gaps

| Phase | Task | Gap |
|-------|------|-----|
| 11 | T110–T112 | ✓ Done: CSRF, SecureHeaders, rate limit, SECURITY.md |
| 11 | T113 | ✓ Done: Session 86400s |
| 11 | T114 | No History API replaceState; URL exposes routes |
| 11 | T115–T116 | ✓ Done: User cost in Manage Users; per-day cost in Team Timesheet |
| 12 | T120–T125 | No email config, templates, CLI remind command |

### 9.3 Phase 13 Spec–Code Analysis (v1.9.2)

| Task | Spec/Clarify | Current Code | Gap |
|------|--------------|--------------|-----|
| **T130 Profile edit** | FR-000b1, Q13.1 | ✓ Done: profile/edit, employee_id, uniqueness check |
| **T131 Dual product flow** | Q13.2–Q13.4: GitHub + manual; leave manual; name/timeline from GitHub | Manual add exists (productAdd); syncFromGitHub exists; no dedicated "Add from GitHub" flow; leave products manual | Add "Add from GitHub" UI; ensure name/timeline pulled from repo |
| **T132 Issues as tasks** | FR-008b: Issues synced under product | GitHubService; ProductController::syncFromGitHub | Verify sync creates tasks from Issues; display in task portal |
| **T133 Product–team mapping** | Q13.5–Q13.6 | ✓ Done: products.team_id, getBillableForUser, product form, billability |
| **T134 Timesheet flow** | Q13.7–Q13.8 | ✓ Done: By Task / By Product toggle; Product→products→tasks→log |
| **T135 SMTP** | Q13.9–Q13.10: Any SMTP; format + Test connection; approval/rejection emails | No approval/rejection email on approve/reject; no SMTP config UI | Admin SMTP config; send email on ApprovalController approve/reject; format validation + Test button |
| **T136 Unified dashboard** | Q13.11–Q13.12 | ✓ Done: Employee→/tasks on login; /home merged view; admin widgets for Manager/Super Admin |

### 9.4 T057 Implementation Detail

**Current**: `ProductModel::removeMember($productId, $userId)` deletes from `product_members` only.

**Required** (Q6.3): Before delete, reassign tasks where `assignee_id = $userId` and `product_id = $productId` to Product Lead.

**Resolution** (T057a): Use `products.product_lead_id`. If null, fallback: first product_member with `role_in_product = 'Product Lead'`, else block removal or use product creator.

**Recommendation**: In `AdminController::productMemberRemove` (or ProductController), before calling `removeMember`:
1. Get product `product_lead_id`
2. If null, get first member with role_in_product = 'Product Lead'
3. Reassign `tasks` where assignee_id=userId and product_id=productId to new assignee_id
4. Then call removeMember

### 9.4 Costing RBAC Fix (Pre–Phase 11)

**Current**: `costing/save` route uses `require_manager`. Both Manager and Super Admin have manager access.

**Required**: Only Super Admin can POST to save. Manager can view (index) but not edit.

**Recommendation**:
- Add route `costing/save` with `require_super_admin` OR
- In `CostingController::save()`, check `$session->get('user_role') === 'Super Admin'` and redirect if not
- Template: hide edit form for Manager; show only for Super Admin (use existing `is_super_admin`)

---

## 10. Edge Cases (Iteration 2 Additions)

| Edge Case | Status | Action |
|-----------|--------|--------|
| Product with no product_lead_id | Schema allows null | T057: Define fallback (first Product Lead in product_members, or block removal) |
| Leave product tasks (assignee_id=null) | Implemented | TimesheetController::log allows; TaskModel::getByAssignee includes leave |
| Email reminder: employee with 0 work days in period | FR-036 | Clarify: Skip or send "no work days" message |
| Approver with 0 pending | FR-037 | Skip sending email |

---

## 11. Recommended Actions (Prioritized)

### Phase 12 (Email) — Blocking for T135

1. **T120**: SMTP config in .env; Admin UI for from/reply-to
2. **T123–T124**: Email templates migration; Super Admin UI
3. **T121–T122, T125**: Employee + approver reminders; CLI + cron

### Phase 13 (v1.9.2) — In Order

4. **T130**: Migration `users.employee_id`; profile/edit route; edit form (first_name, last_name, email, employee_id); uniqueness check
5. **T133**: Migration `products.team_id`; product form team dropdown; billability check (no team = no bill; leave exempt)
6. **T131**: "Add from GitHub" flow; name/timeline from GitHub; leave manual only
7. **T132**: Verify Issues sync; display in task portal
8. **T134**: Timesheet: Product/Task toggle; Product→products→tasks→log; Task→tasks→log (filter by team)
9. **T135**: SMTP format + Test connection; send approval/rejection emails
10. **T136**: Unified dashboard; role-based redirect; merged view

### Lower Priority

11. **T114**: History API replaceState for URL masking
12. **T053a**: requestRework action
13. **T057**: Task reassignment on removeMember
14. **T065**: Migration rollback README
15. **Rework % Total=0**: Add guard in reports

---

**Version**: 1.9.2 | **Created**: 2026-02-19 | **Updated**: 2026-02-20 (Iteration 3: Phase 13 spec–code analysis)  
**Next**: Phase 12 (T120–T125) then Phase 13 (T130–T136)
