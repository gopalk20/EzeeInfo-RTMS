# Clarification Questions: RTMS Baseline Spec

**Created**: 2026-02-19  
**Purpose**: De-risk ambiguous areas before implementation planning  
**Run before**: `/speckit.plan`

---

## How to Use

Answer each question below. Your responses will be captured and used to update the spec before creating the implementation plan. Mark resolved items with `[x]` and add your answer inline or in the **Response** block.

---

## 1. GitHub Integration & Task Sync

### Q1.1 Task source of truth
**Context**: Constitution says GitHub is single source of truth; spec allows Product Lead to "create tasks and milestones" manually.

**Question**: Are manual tasks independent of GitHub, or must they eventually map to a GitHub issue? If independent, how do they appear in reports vs GitHub-synced tasks?

**Response**: [x] GitHub-synced tasks. Tasks originate from GitHub; manual creation supplements or syncs from GitHub.

---

### Q1.2 Status transition detection
**Context**: Spec says "First Commit Pushed → In Progress" and "Branch Merged to Main → Completed."

**Question**: How should the system detect these Git events?
- **A)** GitHub Webhooks (real-time)
- **B)** Scheduled polling of GitHub API
- **C)** Manual status change by Employee (no automatic detection)
- **D)** Other: _[Specify]_

**Response**: [x] A) GitHub Webhooks (real-time)

---

### Q1.3 Branch linking mechanism
**Context**: Employee "links GitHub branch to assigned task."

**Question**: How does branch linking work?
- **A)** Employee enters branch name manually (e.g., `feature/xyz-123`)
- **B)** System lists branches from GitHub; Employee selects one
- **C)** System auto-detects branch from commits (e.g., via GitHub API)
- **D)** Other: _[Specify]_

**Response**: [x] B) System lists branches from GitHub; Employee selects one

---

### Q1.4 Branch reuse
**Context**: Edge case: "What happens when an employee tries to link a branch that is already linked to another task?"

**Question**: Should the system allow one branch linked to multiple tasks, or enforce one branch per task (or per product)?

**Response**: [x] No. One branch per task; enforce one-to-one (branch cannot be linked to multiple tasks).

---

### Q1.5 GitHub sync scope
**Context**: "Link GitHub Repository" to product.

**Question**: What is synced from the linked repository?
- Issues only?
- Issues + Milestones?
- Issues + Milestones + Pull Requests?
- Custom (e.g., labels, projects)?

**Response**: [x] Pull Requests (PRs). Sync scope: Issues + PRs.

---

## 2. Product & Task Configuration

### Q2.1 Maximum Allowed Time
**Context**: Product has "Maximum Allowed Time."

**Question**: Is this limit per task, per product total, or per employee per product? What happens when exceeded—block new time logs, warn only, or audit trail only?

**Response**: [x] No block when exceeded. Warn or audit only; do not block new time logs.

---

### Q2.2 Product vs Project
**Context**: Finance "views task-wise and project-wise time consumption."

**Question**: Are "Product" and "Project" the same entity, or is Project a parent/grouping of multiple Products?

**Response**: [x] No. Product and Project are NOT the same entity. (Project may be parent/grouping of Products—TBD in plan.)

---

### Q2.3 Milestone vs Task
**Context**: Both milestones and tasks exist; tasks can be grouped under milestones.

**Question**: Can a task exist without a milestone? Can a milestone exist without tasks? What is the minimum relationship?

**Response**: [x] Yes. Tasks can exist without milestones; milestones can exist without tasks. Flexible/optional relationship.

---

## 3. D+N Policy & Time Logging

### Q3.1 D+N definition
**Context**: BR-2 – "Timesheet editable only within D+N days from work date."

**Question**: What are the intended default values for D and N? (e.g., D=0, N=3 means editable for 3 days after work date?)

**Response**: [x] Yes. Defaults to be configured (e.g., N=3 days editable after work date). Config key for D and N.

---

### Q3.2 Rework identification
**Context**: Employee "responds to rework requests" and time is "tagged as rework."

**Question**: How is time tagged as rework?
- **A)** All time logged after task is "reopened for rework" is auto-tagged
- **B)** Employee explicitly marks a time entry as rework when logging
- **C)** Product Lead flags task as "rework" and subsequent time is tagged
- **D)** Other: _[Specify]_

**Response**: [x] B) Employee explicitly marks a time entry as rework when logging

---

### Q3.3 Rework request flow
**Context**: "Task reopened for rework."

**Question**: Who reopens the task? How does the Employee get notified? Is there a formal "rework request" entity or just a status change?

**Response**: [x] Status change. Employee finds out via task status update (e.g., status changed to "Rework Requested").

---

## 4. Approval Workflow

### Q4.1 Approval levels
**Context**: Constitution says "multi-level approval"; spec says "Product Lead approves task completion."

**Question**: For MVP, is approval single-level (Product Lead only) or multi-level (e.g., Product Lead → Manager)? If multi-level, define the sequence.

**Response**: [x] Multi-level. Approval is multi-level from the start. (Exact sequence TBD in plan.)

---

### Q4.2 Logical vs formal approval
**Context**: Spec uses "logical approval."

**Question**: Does "logical approval" mean in-app approval only (no external sign-off), or something else? Any distinction from "formal" approval?

**Response**: [x] Ignore. No distinction needed between logical and formal approval.

---

### Q4.3 Who can approve
**Context**: Product Lead and Manager can approve.

**Question**: Can a Product Lead approve tasks in products they don't lead? Can Manager approve any task, or only after Product Lead approval (if multi-level)?

**Response**: [x] No. Only Manager can approve. Product Lead cannot approve tasks. Manager is the approver.

---

## 5. Brownfield / Existing System

### Q5.1 What already exists
**Context**: "Existing RTMS backend, frontend, authentication, and roles already exist."

**Question**: Which of these already exist in the current codebase?
- [ ] User/Employee management
- [ ] Product/Project entity
- [ ] Task entity
- [ ] Timesheet/Time logging
- [ ] GitHub integration
- [ ] Approval workflow
- [ ] Reports (Finance)
- [ ] Role-based UI/filters

**Response**: [x] Need to develop fresh. Nothing exists; build from scratch. All features need to be developed.

---

### Q5.2 Database schema
**Question**: Is there an existing database schema for products, tasks, timesheets? Can it be extended with migrations, or must we work within existing tables?

**Response**: [x] No. Create new schema/tables. No need to extend existing for now.

---

### Q5.3 Authentication & roles
**Question**: Are the four roles (Employee, Product Lead, Manager, Finance) already implemented in the auth layer, or do they need to be added?

**Response**: [x] No. Roles need to be added. Four roles (Employee, Product Lead, Manager, Finance) need to be implemented in auth layer.

---

## 6. Edge Cases & Failure Modes

### Q6.1 GitHub sync failures
**Context**: "How does system handle GitHub sync failures (e.g., rate limit, token expired)?"

**Question**: Desired behavior: Retry automatically, surface error to admin, disable sync until fixed, or other?

**Response**: [x] Disable sync until fixed. Surface error to admin; do not retry automatically.

---

### Q6.2 Timeline exceeded
**Context**: "What happens when timeline (End Date) is exceeded but tasks are incomplete?"

**Question**: Should the system block new time logs, show warning, allow with override, or no special handling?

**Response**: [x] B) Show warning only; allow logging.

---

### Q6.3 Member removal
**Context**: "How does system handle task assignment when employee is removed from product?"

**Question**: Should assigned tasks be unassigned, reassigned to Product Lead, or remain assigned with read-only access? Can removed member still log time on in-progress tasks?

**Response**: [x] Reassign to Product Lead. When employee is removed from product, assigned tasks are reassigned to Product Lead.

---

## 7. Finance & Costing

### Q7.1 Product Lead costing access
**Context**: Product Lead "Cannot Do: See financial costing (unless explicitly allowed)."

**Question**: How is "explicitly allowed" configured? Per product? Per user? System-wide setting?

**Response**: [x] Only Manager is allowed. Product Lead cannot see financial costing; no "explicitly allowed" override.

---

### Q7.2 Report format
**Question**: Are reports exported (CSV, PDF, Excel) or view-only in the UI? Any scheduled/email reports?

**Response**: [x] Both. View in UI + Export (CSV, PDF, Excel).

---

## 8. Post-Plan Clarifications (Optional)

*Questions that emerged during implementation planning. Answer to refine scope.*

### Q8.1 Timeline exceeded (Q6.2 – resolved)
**Context**: Product End Date passed but tasks are incomplete.

**Question**: Desired behavior?
- **A)** Block new time logs
- **B)** Show warning only; allow logging
- **C)** Allow with Manager override
- **D)** No special handling

**Response**: [x] B) Show warning only; allow logging.

---

### Q8.2 Product vs Project
**Context**: Q2.2 said Product ≠ Project; Finance reports mention "project-wise" time.

**Question**: For Finance reports in MVP, should "project" = product (single entity), or do we need a separate Project entity that groups Products?

**Response**: _[Your answer]_

---

### Q8.3 Multi-level approval sequence
**Context**: Clarify said "multi-level"; plan assumed single Manager for MVP.

**Question**: For MVP, is single Manager approval sufficient, or do you need a defined sequence (e.g., Product Lead recommends → Manager approves)?

**Response**: _[Your answer]_

---

### Q8.4 Who reopens task for rework?
**Context**: Q3.3 said Employee finds out via status change; spec says "task reopened for rework."

**Question**: Who changes status to "Rework Requested"? Product Lead, Manager, or both?

**Response**: _[Your answer]_

---

## 9. Auth, Profile & Super Admin (User-Requested 2026-02-19)

### Q9.1 User Profile
**Response**: [x] User profile shows: Name, email, current role, team name. All logged-in users can view their profile.

### Q9.2 Logout
**Response**: [x] All users can log out from the application.

### Q9.3 Password Reset
**Response**: [x] Users can reset their own password (self-service). Super Admin can reset any other user's password without needing the current password.

### Q9.4 Super Admin
**Response**: [x] Super Admin is a fifth role with full access. Can add new users and reset any user's password.

### Q9.5 Add User (Super Admin)
**Response**: [x] Super Admin adds users with: username, email, first name, last name, current role (selection), team name (selection), phone number.

---

## Summary

| Category        | Questions | Resolved |
|-----------------|-----------|----------|
| GitHub Integration | 5     | 5        |
| Product & Task  | 3         | 3        |
| D+N & Rework    | 3         | 3        |
| Approval        | 3         | 3        |
| Brownfield      | 3         | 3        |
| Edge Cases      | 3         | 3            |
| Finance         | 2         | 2        |
| Post-Plan       | 4         | 0        |
| Auth & Profile  | 5         | 5        |

**Next step**: Answer Q6.2 and optional Post-Plan questions to refine the implementation. Plan already created; assumptions documented for unresolved items.

---

## Implementation Status (2026-02-19)

**Scope implemented**: Time entry, products, tasks, milestones, costing, GitHub sync, approval, reports, performance, timesheet workflow, reporting structure, product/task CRUD, access control, user enable/disable, Vertex UI.

| Item | Status |
|------|--------|
| Time entry form | ✓ TimesheetController; task, work_date, hours, is_rework, status; success message on submit |
| BR-1 (daily limit) | ✓ ConfigService; validate on save |
| BR-3 (block locked) | ✓ Block log for locked tasks |
| Timesheet status | ✓ pending_approval, approved; edit before approval |
| Timesheet view | ✓ daily/weekly/monthly; Project Name \| Time \| Total; timesheet/view.tpl |
| Reporting manager | ✓ users.reporting_manager_id; AdminController setReportingManager; profile shows reporting manager |
| Product Lead approvals | ✓ Product Lead sees Approval page; approve timesheet for product members |
| Team timesheet | ✓ /timesheet/team; consolidated view for Lead/Manager reports |
| Products | ✓ list, view, syncFromGitHub; Super Admin: add/edit/delete; delete blocks if users/tasks mapped |
| Product access control | ✓ Grant/revoke product_members; AdminController productMemberAdd/Remove |
| Task CRUD | ✓ Manager/Super Admin: add/edit/delete on product view; delete blocks if time entries mapped |
| User enable/disable | ✓ users.is_active; blocked login; AdminController toggleActive |
| Costing, Approval, Reports | ✓ As before |
| Vertex UI | ✓ Login page; layout (header, sidebar); dashboard cards |

---

**Version**: 1.5.0 | **Created**: 2026-02-19 | **Updated**: 2026-02-19 (timesheet workflow, reporting, CRUD, access control, Vertex UI)
