# Analysis Report: RTMS Implementation — Iteration 1

**Date**: 2026-02-19  
**Scope**: Tasks actionability, missing deliverables, edge cases, backward compatibility  
**Input**: tasks.md, spec.md, plan.md, constitution.md, existing codebase

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

## 7. Implementation Status (2026-02-19)

**Implemented**:
- Auth, login, logout, profile, password reset; is_active blocks disabled users
- Timesheet: status (pending_approval, approved); success message; edit before approval; daily/weekly/monthly view
- Reporting: users.reporting_manager_id; time entries routed to Manager/Product Lead; profile shows reporting manager
- Approval: Product Lead + Manager + Super Admin; task completion + timesheet approvals
- Team timesheet: /timesheet/team; consolidated view for Lead/Manager reports
- Products: list, view, sync; Super Admin CRUD (add/edit/delete); grant/revoke product_members
- Tasks: list; Manager/Super Admin add/edit/delete on product view; delete blocks if time entries mapped
- User management: Super Admin add user, reset password, enable/disable, modify reporting manager
- Vertex UI: login page, layout (header, sidebar), dashboard cards
- Costing, Milestones, Reports: as before

**TODO**: T053a (requestRework), T057a (Product Lead resolution), Webhooks, PDF/Excel export, D+N edit UI

---

**Version**: 1.5.0 | **Created**: 2026-02-19 | **Updated**: 2026-02-19  
**Next**: Webhooks, PDF/Excel export, Forgot Password flow
