# Security Policy (FR-034)

## Overview

This document describes security practices for the Resource Timesheet Management System (RTMS) for voluntary disclosure and vulnerability reporting.

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 1.9.x   | :white_check_mark: |

## Security Practices

### Encryption

- **Passwords**: Stored using bcrypt via PHP `password_hash()` / `password_verify()`. No plain-text passwords.
- **Sensitive config**: SMTP credentials and API tokens stored in `.env` (not in version control). `.env` should have restrictive file permissions (e.g., `chmod 600`).
- **In transit**: HTTPS required in production (`app.forceGlobalSecureRequests = true`).

### Access Controls

- **Authentication**: Session-based; individual login per user. Disabled users (`is_active = 0`) cannot log in.
- **Authorization (RBAC)**: Five roles (Employee, Product Lead, Manager, Finance, Super Admin). Route filters enforce role checks before business logic.
- **User cost edit**: Only Super Admin can edit `resource_costs`; Manager has view-only access.
- **Sensitive routes**: Protected by `require_manager`, `require_super_admin`, `require_product_lead_or_manager`, etc.

### Audit

- **Time entries**: Status (pending_approval, approved); edit history within D+N window.
- **Approvals**: Approver ID and timestamp recorded in approvals table.
- **Logging**: Failed logins, errors logged to `writable/logs/`.

### Input Validation & Output Encoding

- **Server-side validation**: CodeIgniter validation rules on forms (e.g., email, required fields).
- **Parameterized queries**: CodeIgniter Query Builder uses bound parameters (no raw SQL concatenation).
- **XSS prevention**: Smarty templates use `|escape` on all user-provided output.

### Session & Cookies

- **Session idle**: 24 hours (86400 seconds); any page load refreshes session.
- **Cookie flags**: HttpOnly and SameSite=Lax enabled; Secure set in production via `cookie.secure = true`.
- **CSRF**: Token validation on POST requests.

### Rate Limiting

- **Login**: 5 attempts per minute per IP address.

## Reporting a Vulnerability

If you discover a security vulnerability, please report it responsibly:

1. **Do not** disclose publicly until the issue has been addressed.
2. Email the maintainers with:
   - Description of the vulnerability
   - Steps to reproduce
   - Potential impact
3. We will acknowledge within 48 hours and work on a fix.
4. We will credit you in the disclosure (with your permission) once the fix is released.

## Deployment Checklist

- [ ] HTTPS enabled at reverse proxy or web server
- [ ] `.env` has `app.forceGlobalSecureRequests = true` and `cookie.secure = true`
- [ ] `.env` file permissions restricted (e.g., `chmod 600`)
- [ ] Database credentials stored in `.env`, not in code
- [ ] `CI_ENVIRONMENT = production` in production `.env`
- [ ] Debug toolbar disabled in production

---

*Version 1.9.1 | Last updated 2026-02-19*
