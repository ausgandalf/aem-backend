# WRBLO — Application Management (Backend API)

Laravel REST API powering WRBLO's grant application workflow: public "Quick Apply" intake, applicant dashboard, staged review pipeline, role-based administration, and queued transactional email.

The frontend is a separate Next.js app (see `../frontend`). This backend is a **stateless-ish JSON API** consumed by that SPA using cookie-based session auth (Laravel Sanctum).

---

## Tech stack

| Concern | Choice |
|---|---|
| Language / framework | PHP 8.3 · Laravel 13 |
| Database | PostgreSQL |
| Auth | Laravel Sanctum (SPA, httpOnly session cookies) |
| Roles & permissions | spatie/laravel-permission |
| Queue | Database driver (`jobs` / `failed_jobs` tables) |
| Cache / session | Database driver |
| Mail (local) | Mailpit (SMTP on `127.0.0.1:1025`) |

---

## What's implemented

- **Authentication** — register, login, logout, `me`, email verification, forgot/reset password. All email is **queued** and rendered from custom templates.
- **RBAC** — 10 roles (applicant, evaluator, pcmu_officer, eco_analyst, approvals_officer, board_member, dcf_officer, finance_officer, admin, marketing). An `active` middleware blocks pending/blocked users; `role:admin` guards admin routes.
- **Admin — Users** — list/search/paginate, view, activity logs, allow/block, edit, admin set-password, toggle email-verified.
- **Admin — Organizations** — full CRUD with registered + operating addresses, contact, financials, status.
- **Quick Apply (public)** — one endpoint creates (or reuses) the applicant account, creates/links the organization, and creates a **draft** application, then queues verification + reset + confirmation emails.
- **Applicant applications** — list own applications, create, view (with progress), edit-and-Save / edit-and-Submit while at the `submit` stage.
- **Workflow engine** — every stage move goes through `Application::recordTransition()`, which updates `prev_*`/`current_*`, writes an `application_logs` audit row, and upserts a per-stage `progresses` snapshot.
- **Email templating** — file-based HTML + text templates with `{{ VAR }}` substitution and a shared WRBLO layout (see below).

> Scaffolded / roadmap: `stages`, `sectors`, and `inspections` tables exist; the reviewer-facing side (evaluator/PCMU/ECO/etc. acting on stages, inspection checklists) is the next build phase.

---

## Requirements

- PHP **8.3+** with the usual Laravel extensions (`pdo_pgsql`, `mbstring`, `openssl`, …)
- Composer
- PostgreSQL 14+
- [Mailpit](https://github.com/axllent/mailpit) (or any SMTP server) for local email

---

## Setup

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
```

Create the database and role (example — match your `.env`):

```sql
CREATE ROLE arm_user WITH LOGIN PASSWORD 'wrblo';
CREATE DATABASE arm OWNER arm_user;
```

Configure `.env` (see **Environment** below), then build the schema and seed:

```bash
php artisan migrate:fresh --seed
```

The seeder creates the 10 roles, the 10 workflow stages, a few sample organizations, and an **admin user**:

```
email:    damon@wrblo.org
password: Admin123!
```

---

## Running (local)

Three processes, each in its own terminal:

```bash
# 1. API server (http://localhost:8000)
php artisan serve

# 2. Queue worker — REQUIRED for any email to actually send
php artisan queue:work --tries=3

# 3. Mailpit — catches outgoing mail at http://localhost:8025
mailpit
```

> The web request only **enqueues** email; the worker sends it. No worker = mail sits in the `jobs` table unsent. **Restart the worker after editing any queued class** (it holds code in memory).

---

## Environment

Key variables (in addition to the Laravel defaults):

```env
DB_CONNECTION=pgsql
DB_DATABASE=arm
DB_USERNAME=arm_user
DB_PASSWORD=wrblo

# SPA auth — must include the frontend origin(s)
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:3000,127.0.0.1,127.0.0.1:3000
SESSION_DOMAIN=localhost
FRONTEND_URL=http://localhost:3000   # used for CORS, reset links, email links

QUEUE_CONNECTION=database
CACHE_STORE=database

# Mail (local Mailpit)
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_LOGO_URL="${FRONTEND_URL}/logo.png"   # logo shown in email layout

# Inbox that gets a notice when an application is submitted for review
APPLICATION_INTAKE_EMAIL=intake@wrblo.org
```

---

## Domain model

- **Application status** (`App\Enums\ApplicationStatus`): `pending`, `in_progress`, `on_hold`, `passed`, `rejected`.
- **Stages** (ordered): `submit → evaluation_1 → audit → evaluation_2 → eco_assessment → legal_review → board_validation → dcf_check → payout_processing → payout_complete`. Served cached (`Stage::cached()`), auto-flushed on change.
- **Applications** carry `current_stage`/`current_status` and `prev_stage`/`prev_status`, plus jsonb `project_details`, `organization_details`, `metadata`.
- **progresses** — one row per (application, stage) holding the latest status/note (avoids `GROUP BY` over logs).
- **application_logs** — append-only audit trail (stage_key, status, description, actor).

---

## API overview

Base path: `/api`. Cross-origin requests use Sanctum's SPA flow (client must hit `GET /sanctum/csrf-cookie` before state-changing calls).

**Public**
```
POST /register            POST /login             POST /forgot-password
POST /reset-password      GET  /roles             GET  /organizations   (Quick Apply combobox)
POST /apply               (Quick Apply)           GET  /email/verify/{id}/{hash}
```

**Authenticated (`auth:sanctum` + `active`)**
```
POST /logout   GET /me   PATCH /profile   PUT /password   POST /email/resend
GET  /stages
GET  /applications        POST /applications
GET  /applications/{id}   PATCH /applications/{id}   (Save / Submit)
```

**Admin (`+ role:admin`, prefix `/admin`)**
```
GET/PATCH /users …   /users/{id}/allow|block|verify-email   PUT /users/{id}/password   GET /users/{id}/logs
GET/POST/PATCH/DELETE /organizations …
```

Run `php artisan route:list --path=api` for the authoritative list.

---

## Project layout (app-specific)

```
app/
  Enums/ApplicationStatus.php
  Http/Controllers/            AuthController, ProfileController, RoleController,
                               StageController, PublicOrganizationController, ApplicationController,
                               Admin/UserController, Admin/OrganizationController
  Models/                      User, UserLog, Organization, Application, ApplicationLog,
                               Progress, Stage
  Mail/TemplatedMail.php       generic mailable (raw html + text, no Blade)
  Notifications/               ApplicationReceived, NewApplicationSubmitted,
                               VerifyEmailQueued, ResetPasswordQueued   (all queued)
  Support/EmailTemplate.php    {{ VAR }} renderer + layout wrapper
resources/mail-templates/      layout.{html,txt} + one {html,txt} pair per email
database/seeders/              Role, Stage, Organization, Admin
```

### Email templates

Emails are plain files, editable without touching PHP:

```
resources/mail-templates/
  layout.html / layout.txt          shared WRBLO header/footer, {{ CONTENT }} slot
  application-received.{html,txt}    {{ NAME }} {{ PROJECT_TITLE }} {{ LOGIN_URL }}
  new-application-submitted.{html,txt}
  verify-email.{html,txt}            {{ NAME }} {{ ACTION_URL }}
  reset-password.{html,txt}          {{ NAME }} {{ ACTION_URL }} {{ EXPIRE }}
```

`EmailTemplate::render($name, $vars)` substitutes `{{ VAR }}`, injects the body into the layout, and returns `['html' => …, 'text' => …]`. Globals (`APP_NAME`, `YEAR`, `LOGO_URL`, `APP_URL`) are always available.

---

## Notes & gotchas

- **Never cache Eloquent models** — cache plain arrays and rehydrate (see `Stage::cached()`), otherwise the database/file cache round-trips into a broken `__PHP_Incomplete_Class`.
- **Guarded fields** (e.g. `email_verified_at`, `password`) are not mass-assignable — set them directly, not via `update([...])`.
- `.env` values with spaces **must be quoted** (`APP_NAME="WRBLO ARM"`), or the whole file fails to parse.
