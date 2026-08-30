# eschoolAI — Multi-Tenant SaaS School Management & AI Education Platform

[![Laravel](https://img.shields.io/badge/Laravel-13%2B-FF2D20?style=flat&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=flat&logo=php)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![Tests](https://img.shields.io/badge/Tests-57%20Passing%20(303%20Assertions)-10B981?style=flat)]()

**eschoolAI** is an enterprise-grade multi-tenant SaaS platform built to power thousands of schools with modern school management and grounded AI pedagogical modules.

📖 **Complete A-to-Z End-User & Developer Documentation**: See [DOCUMENTATION.md](DOCUMENTATION.md)

---

## 🌟 Key Highlights

- **Strict Multi-Tenancy**: Zero data leakage across schools; automatic tenant scoping via Eloquent scopes, Sanctum bearer tokens, headers, and subdomains.
- **8 RBAC Roles**: Super Admin, School Admin, Principal, Teacher, Student, Parent, Accountant, and Staff.
- **SaaS Billing & Subscriptions**: Tiered plans (Starter, Professional, Enterprise), dynamic student/storage/AI quotas, discount coupon engine (`WELCOME20`, `LAUNCH50`), and automated invoice generation.
- **16 Core Academic Modules**:
  1. Executive Dashboard (Live database metrics & financials)
  2. Student Directory & Admission Management
  3. Faculty & Teacher Directory
  4. Classes, Sections & Class Teacher Mentorship
  5. Bulk Attendance Register (Daily & Subject-wise)
  6. Finance, Invoicing, Concessions & Payment Collection
  7. Homework & Assignment Workflow
  8. Weekly Class Timetable Matrix
  9. Campus Notice Board & Broadcasts
  10. Real-time Communication & Direct Messaging
  11. Reports, Audit Trail & CSV Exporters
  12. Granular Roles & Permissions Matrix
  13. Subject & Class Allocation
  14. Examination, Question Bank & Student Report Cards
  15. Study Materials & Digital Library
  16. Database Snapshots & GDPR/FERPA Compliance
- **Unified AI Pedagogical Studio (Vertical Tab Layout)**:
  - 💬 **Natural Query Chat Assistant**: Interactive live database querying.
  - 🎓 **Bloom's Lesson Planner**: 4-phase pedagogical curriculum plans with Bloom's taxonomy checkmarks.
  - 📝 **Exam Question Paper Synthesizer**: Balanced blueprint matrix with difficulty distributions.
  - 📄 **Differentiated Worksheet Studio**: Adaptive 3-tier exercises with teacher solution keys.
  - 🔍 **Answer Sheet OCR & Evaluator**: Automated OCR handwritten scoring & gradebook approval.
  - 📢 **Institutional Circular Generator**: Campus announcements with official school letterheads.
  - 📚 **Tenant Vector RAG Studio**: Semantic search over textbooks with Qdrant vector isolation.
  - 📜 **AI History & Word (.doc) Exporters**: Native Word file downloads and token telemetry.
- **Mobile REST API Bridge**: Flutter-compatible REST API Bridge with offline delta sync.
- **Realtime WebSocket Hub**: Authenticated private channels and emergency campus broadcasts.

---

## 🚀 Quick Start

### 1. Requirements
- PHP 8.3+ with `sqlite3` or `pdo_mysql` extensions enabled
- Composer

### 2. Installation
```bash
git clone https://github.com/AmitBMakwana/eschoolAI.git
cd eschoolAI

composer install
```

### 3. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Database Setup & Seed
```bash
php artisan migrate:fresh --seed
```

### 5. Start Development Server
```bash
php artisan serve
```

Access the application:
- **Interactive Multi-Role Portal**: `http://127.0.0.1:8000/portal`
- **Sign In Page**: `http://127.0.0.1:8000/login`
- **Component Showcase**: `http://127.0.0.1:8000/showcase`
- **REST API Docs**: `http://127.0.0.1:8000/docs`

### 6. Run Test Suite
```bash
php artisan test
# Output: 57 passed (303 assertions)
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

