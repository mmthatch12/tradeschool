# TradeSchool Demo — Enrollment & Tuition Payment Portal

> **Architected by Matt Thatcher, implemented with [Claude Code](https://claude.ai/code)**

A Laravel SaaS demo application showcasing a trade school's enrollment and tuition payment workflows. Built as a portfolio project to demonstrate AI-augmented Laravel development using current best practices.

---

## Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 13, PHP 8.4 |
| Admin Panel | Filament 5 |
| Reactive UI | Livewire 4 + Alpine.js |
| Styling | TailwindCSS 4 (Vite) |
| Database | SQLite (local dev) / MySQL (production) |
| Auth | Laravel Sanctum |
| Testing | Pest PHP 4 |
| Queue | Database driver (swap to Redis/Horizon in production) |

---

## Features

### Public Portal (no login required)
- **Application form** — multi-field enrollment application with document upload (ID, transcripts)
- **Application status page** — tokenized URL lets applicants track their status without an account
- **Student payment portal** — tokenized URL grants enrolled students access to their payment dashboard

### Student Payment Dashboard
- View tuition balance, payment schedule, and progress bar
- Set up a monthly / bi-weekly / weekly installment plan
- Pay individual installments (mock gateway — always succeeds, returns `MOCK-{txn_id}`)

### Filament Admin Panel (`/admin`)
- **Applications** — list, review, approve (auto-creates Student + Enrollment + portal token), or deny with notes
- **Students** — full CRUD, enrollment count
- **Enrollments** — create from admin, inline "Setup Payment Plan" action
- **Payments** — list all payments with "Mark Paid" row action
- **Dashboard** — stats widget showing pending applications, active enrollments, overdue payments, and monthly revenue

---

## Architecture Decisions

### Service Layer
Business logic lives in `app/Services/` rather than controllers or models:
- **`ApplicationService`** — handles submit (generates application token), approve (creates Student + Enrollment + portal token), and deny
- **`PaymentService`** — creates full installment schedule on plan setup; processes mock payments; auto-completes plans when fully paid

Controllers stay thin. Livewire components call services via dependency injection.

### Tokenized Public Portal (no second auth system)
Rather than building a separate student auth guard, each Student gets a `portal_token` (UUID) set when their application is approved. The student portal lives at `/portal/{token}`. Similarly, applicants get an `application_token` to check status at `/apply/{token}/status`.

This keeps the public-facing layer stateless and simple while still being secure enough for a demo — swap in proper auth (magic link / OTP) before production.

### Tenant Isolation
All models carry a `school_id` foreign key. This is the simplest form of multi-tenancy — no separate databases, no Eloquent scopes — sufficient for a demo. For production, add a global scope or use a package like `stancl/tenancy`.

### Jobs for Async Work
Email notifications and payment processing are dispatched as queued jobs:
- `SendApplicationConfirmationEmail` — fires on application submit
- `ProcessPaymentJob` — called by `PaymentService` for async gateway calls

The queue connection is `database` locally. In production, swap to Redis + Horizon.

### Policies
`ApplicationPolicy` and `PaymentPolicy` guard admin actions. The Filament resource actions check these via `authorize()`.

---

## Requirements

- PHP 8.4+
- Composer
- Node.js 18+
- SQLite (included with most PHP installs — no separate DB server needed)

## Local Setup

```bash
# 1. Install PHP dependencies
composer install

# 2. Copy environment and generate key
cp .env.example .env
php artisan key:generate

# 3. Create the SQLite database file and run migrations + seed
touch database/database.sqlite
php artisan migrate --seed

# 4. Install JS dependencies and build assets
npm install && npm run build

# 5. Start the dev server
php artisan serve
```

### Demo Credentials
| URL | Credential |
|---|---|
| `http://localhost:8000/admin` | `admin@apextrade.test` / `password` |
| `http://localhost:8000/apply` | No login — public form |
| `http://localhost:8000/portal/{token}` | Get token from a Student record in the admin |

---

## Running Tests

```bash
php artisan test
```

15 Pest feature tests covering:
- Application form submission and token generation
- Application approval → creates Student + Enrollment + portal token
- Application denial with reviewer notes
- Payment plan creation with correct installment math
- Payment processing (mock gateway, transaction ID)
- Auto-completion of payment plan when all payments are paid
- Livewire portal actions (setup plan, pay installment)

---

## Project Structure

```
app/
├── Filament/
│   ├── Resources/          # ApplicationResource, StudentResource,
│   │   │                   # EnrollmentResource, PaymentResource
│   │   └── */Pages/        # List, Create, Edit, View page classes
│   └── Widgets/
│       └── EnrollmentStatsWidget.php   # Dashboard stats
├── Http/Requests/          # StoreApplicationRequest, MakePaymentRequest
├── Jobs/                   # SendApplicationConfirmationEmail, ProcessPaymentJob
├── Livewire/               # ApplicationForm, ApplicationStatus, StudentPortal
├── Models/                 # School, Program, Student, Application,
│                           # Enrollment, PaymentPlan, Payment, User
├── Policies/               # ApplicationPolicy, PaymentPolicy
└── Services/               # ApplicationService, PaymentService
```

---

## What "Production Ready" Would Add

- Real Stripe integration (swap `PaymentService::processPayment()` for Stripe Charge/PaymentIntent)
- Student email authentication (magic link or OTP instead of bare UUID token)
- Full Eloquent global scopes for tenant isolation
- Redis queue + Laravel Horizon for job monitoring
- S3/Cloudflare R2 for document storage (currently local disk)
- Role-based permissions within Filament (admin vs. staff vs. read-only)
