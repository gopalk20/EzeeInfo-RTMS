<!-- ================================================================================
SYNC IMPACT REPORT - Constitution v1.0.0
================================================================================
Date: 2026-02-19
Version Change: N/A → v1.0.0 (Initial constitution creation)
Ratification Date: 2026-02-19

PRINCIPLES DEFINED:
✓ I. Data Integrity & Auditability (NON-NEGOTIABLE) - Immutability after final approval; full audit trail
✓ II. Configuration Over Hard-Coding - BR-1, BR-2, and business rules configurable
✓ III. Role-Based Access Control (RBAC) - Four roles (Employee, Product Lead, Manager, Finance)
✓ IV. GitHub as Single Source of Truth for Tasks - Unidirectional sync; manual tasks supplement only
✓ V. Approval Workflow Integrity - Sequential, stateful, auditable multi-level approvals
✓ VI. Calculated Values Are Deterministic - BR-4, BR-5 formulas; no manual override of calculations
✓ VII. Testability & Traceability - Automated tests for critical paths; migrations versioned
✓ VIII. Simplicity & Maintainability - PHP 8.4 + CodeIgniter 4 + Smarty + MySQL only

ADDITIONAL SECTIONS:
✓ Data Models - Documents User, Employee, Task, TimesheetEntry, ApprovalWorkflow, and related entities
✓ Business Rules - BR-1 through BR-5 (daily limit, D+N policy, locking, rework %, hourly cost)
✓ Supported Roles - Defines Employee, Product Lead, Manager (Admin), Finance capabilities and constraints
✓ Technology Stack - PHP 8.4, CodeIgniter 4, Smarty 5, MySQL 8.x, GitHub API
✓ Security & Compliance - Session auth, CSRF, bcrypt/argon2, audit trail, data retention
✓ Governance - Amendment process, compliance review procedures

TEMPLATE ALIGNMENT STATUS:
✓ plan-template.md - Contains "Constitution Check" gate; templates align with defined principles
✓ spec-template.md - Requires features to follow constitutional constraints
✓ tasks-template.md - Tasks must be organized per constitution principles
✓ checklist-template.md - Pre-implementation checklist should reference constitution principles

FOLLOW-UP ACTIONS:
- None: Initial constitution is complete with no deferred placeholders
- Projects should now reference this constitution when creating feature specs and plans
- All implementations must validate against the eight core principles and business rules

COMMIT MESSAGE: docs: create constitution v1.0.0 (RTMS resource timesheet management framework)
================================================================================ -->

# Resource Timesheet Management System (RTMS) Constitution
<!-- Spec Kit Constitution for the RTMS project -->

## Purpose & Scope

**Purpose**: Establish the authoritative principles, rules, and standards governing the Resource Timesheet Management System—a web-based, centralized platform for product-based companies to manage employee timesheets, costing, milestone tracking, GitHub integration, approval workflows, performance tracking, and reporting.

**Scope**: All development, configuration, and operational decisions for RTMS must align with this constitution. External integrations (GitHub, HR systems) must adhere to defined contracts.

**Definitions**:
- **D+N Policy**: Timesheet entries are editable only for N calendar days after the work date. Configurable per organization.
- **Rework**: Additional effort spent due to task reopening, defect fixes, or requirement corrections—measured for quality and cost impact.
- **Milestone**: A time-bounded deliverable tied to one or more tasks with release status tracking.

## Core Principles

### I. Data Integrity & Auditability (NON-NEGOTIABLE)

All timesheet and approval data is immutable after final approval. Every change to editable records must be logged with: user, timestamp, before/after state. Locked (final-approved) entries cannot be modified—corrections require formal reversal workflow. Financial and performance reports must be traceable to source records.

### II. Configuration Over Hard-Coding

Business rules must be configurable where specified: daily hours limit (BR-1), D+N edit window (BR-2), standard hours, working days. Configuration lives in database or `.env`—never in application code. Defaults exist for development; production overrides are explicit.

### III. Role-Based Access Control (RBAC)

Exactly four roles: **Employee**, **Product Lead**, **Manager (Admin)**, **Finance**. Every feature enforces role checks. No implicit elevation; no shared credentials. Each role has a minimum-necessary permission set defined in the access matrix.

### IV. GitHub as Single Source of Truth for Tasks

Task metadata (issue ID, title, status, labels) originates from GitHub. Manual tasks created by Product Lead supplement—do not duplicate—GitHub issues. Synchronization is unidirectional (GitHub → RTMS) unless explicitly scoped for bidirectional updates. Sync failures must be logged and surfaced.

### V. Approval Workflow Integrity

Multi-level approval is sequential and stateful. No skip-level approvals without explicit override (audit required). Each approval level records approver, timestamp, and decision. Rejection returns to previous state with feedback; resubmission creates a new approval chain entry.

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

- **User**: Represents authenticated system participants with email, password (bcrypt/argon2-hashed), role (Employee, Product Lead, Manager, Finance), and profile data
- **Employee**: Represents employee master data, cost center assignment, and costing configuration linkage
- **Task**: Represents work units from GitHub sync or manual creation; linked to milestones; tracks release status and feedback status
- **Milestone**: Represents time-bounded deliverables tied to tasks; tracks release status and feedback status
- **TimesheetEntry**: Represents daily work logged against tasks; subject to D+N policy and approval workflow
- **ReworkLog**: Tracks additional effort from task reopening or corrections; feeds Rework % calculation
- **ApprovalWorkflow**: Multi-level approval state machine; records approver, timestamp, and decision per level
- **CostConfig**: Resource costing configuration; monthly cost, working days, standard hours
- **PerformanceScore**: Resource performance aggregate from feedback, rework %, and delivery metrics
- **ReportConfig / ReportRun**: Task-wise costing reports; monthly and yearly performance reports

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

The RTMS enforces four roles with distinct capabilities:

- **Employee**: Can view and edit own timesheet (within D+N policy), view own reports. Cannot create tasks, approve timesheets, or access costing/employee config.
- **Product Lead**: Can view and edit own timesheet, create manual tasks, approve timesheets (L1), configure GitHub sync, view team reports. Cannot manage employee master, costing config, or user/role management.
- **Manager (Admin)**: Full access to employee master, approval (L2+), GitHub sync, costing config, all reports, and user/role management. Cannot be bypassed by route-level exceptions.
- **Finance**: Can view employee master and reports, manage costing config. Cannot edit timesheets or approve workflows.

RBAC rules are evaluated at the filter/controller level and cannot be bypassed. Release status and feedback status are tracked on task and milestone level.

## Technology Stack

| Layer | Technology | Constraint |
|-------|------------|------------|
| Backend | PHP 8.4 | Use typed properties, strict types where applicable |
| Framework | CodeIgniter 4 | Follow PSR-4; use built-in validation, filters, migrations |
| Templating | Smarty 5 | Logic-free templates; assign only; no raw SQL in templates |
| Database | MySQL 8.x | UTF8MB4; transactions for multi-step operations |
| External API | GitHub REST/GraphQL | Rate limiting, token rotation, error handling |

**Integration Points**: GitHub (issues, milestones, labels); future: HR/payroll systems via defined APIs.

## Security & Compliance

- **Authentication**: Session-based; secure cookie flags; CSRF on all state-changing requests
- **Authorization**: Filter-based role checks on every controller; no client-side-only enforcement
- **Sensitive Data**: Passwords hashed (bcrypt/argon2); tokens in env; no secrets in logs
- **Audit Trail**: Approval actions, role changes, config changes logged with user and timestamp
- **Data Retention**: Define retention for timesheet data; comply with local labor regulations

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
- RBAC is enforced for all four roles
- GitHub remains single source of truth for tasks
- Approval workflow integrity is preserved
- Calculated values use documented formulas (BR-4, BR-5)
- Technology stack constraints are respected
- Business rules BR-1 through BR-5 are enforced

**Version**: 1.0.0 | **Ratified**: 2026-02-19 | **Last Amended**: 2026-02-19
