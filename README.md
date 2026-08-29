# AI SchoolOS — Multi-Tenant SaaS School Management & AI Education Platform

[![Laravel](https://img.shields.io/badge/Laravel-12%2B-FF2D20?style=flat&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat&logo=php)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![Tests](https://img.shields.io/badge/Tests-24%20Passing-10B981?style=flat)]()

**AI SchoolOS** is an enterprise-grade multi-tenant SaaS platform built to power thousands of schools with modern school management and grounded AI education modules.

---

## 🌟 Key Highlights

- **Strict Multi-Tenancy**: Zero data leakage across schools; automatic tenant scoping via Eloquent scopes, Sanctum bearer tokens, headers, and subdomains.
- **8 RBAC Roles**: Super Admin, School Admin, Principal, Teacher, Student, Parent, Accountant, and Staff.
- **SaaS Billing & Subscriptions**: Tiered plans (Starter, Professional, Enterprise), dynamic student/storage/AI quotas, discount coupon engine (`WELCOME20`, `LAUNCH50`), and automated invoice generation.
- **Core Academic Management**:
  - Classes, Sections, Subjects & Teacher Allocations
  - Student Directory, Admission & Multi-child Parent Linkage
  - Fast Bulk Attendance Marking & Daily Roster Summaries
  - Homework & Assignment Workflow (Teacher assignment, Student submission, Grading/Review)
  - Timetable Scheduling & Matrix
  - Notice Board & Circular Distribution
  - Direct Messaging & Threaded Communication
- **AI Education Subsystem (Provider Abstracted)**:
  - AI Lesson Planner, Question Paper Generator, Worksheet Generator, Answer Sheet Evaluation, Circular Generator, Student Report Analysis.
  - Multi-provider layer supporting OpenAI, Claude, Gemini, and local Ollama.
- **Modern Education SaaS Design System**:
  - Interactive Component Showcase (`/showcase` & `/design-system`) with full tokens, responsive app shell, toast notifications, modals, and loading states.

---

## 📁 Repository Structure

```
├── .agents/                 # Orchestration scaffold (Subagent roles, skills, and workflows)
├── app/
│   ├── Http/Controllers/   # API V1 Controllers (Auth, Tenant, Billing, Academic, Student, etc.)
│   ├── Http/Middleware/    # TenantMiddleware, SubscriptionFeatureMiddleware
│   ├── Models/             # TenantScoped Eloquent Models
│   ├── Services/           # BillingService, AuditLogService
│   └── Tenancy/            # TenantContext, TenantScope, TenantResolver, TenantScoped Trait
├── database/
│   ├── migrations/         # Tenancy, RBAC, Billing, Academic & Core Module Migrations
│   └── seeders/            # Role, Plan, Tenant, and Academic seeders
├── docs/                   # Full architectural blueprints & PRD specifications
├── public/
│   ├── css/                # schoolos-design-system.css
│   └── js/                 # schoolos-ui.js
├── resources/views/        # welcome.blade.php (Dashboard), showcase.blade.php
└── tests/Feature/          # TenantIsolationTest, AuthTest, BillingTest, CoreAcademicModulesTest
```

---

## 🚀 Quick Start

### 1. Requirements
- PHP 8.2+ with `sqlite3` or `pdo_mysql` extensions enabled
- Composer
- Node.js & NPM

### 2. Installation
```bash
git clone https://github.com/AmitBMakwana/eschoolAI.git
cd eschoolAI

composer install
npm install
```

### 3. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Database Setup & Seed
```bash
php artisan migrate --seed
```

### 5. Start Development Server
```bash
php artisan serve
```

Access the application:
- **Interactive Portal & Dashboard**: `http://127.0.0.1:8000/`
- **Design System Showcase**: `http://127.0.0.1:8000/showcase`

### 6. Run Test Suite
```bash
php artisan test
```

---

## 🔑 Demo Accounts (Password: `password123`)

| Persona | Email | Scope |
|---|---|---|
| **Platform Super Admin** | `superadmin@schoolos.com` | Global SaaS Control Plane |
| **School Admin (Greenfield)** | `admin@greenfield.edu` | Greenfield International School |
| **Science Teacher** | `teacher@greenfield.edu` | Class 8 Science & Homeroom |
| **Student (Alex Miller)** | `student@greenfield.edu` | Class 8-A |
| **Parent (Robert Miller)** | `parent@greenfield.edu` | Linked to Alex Miller |
| **School Admin (Oakridge)** | `admin@oakridge.edu` | Oakridge Academy (Isolated) |

---

## 📄 License
This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
