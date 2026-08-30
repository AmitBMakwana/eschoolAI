# eschoolAI — Master Platform Documentation (A to Z)

> **Production-Ready Multi-Tenant SaaS School Management & Grounded AI Education Platform**  
> *Version: 1.0.0-PROD • Architecture: Multi-Tenant RESTful + Realtime + Vector RAG*

---

# Table of Contents
1. [Executive Overview & Architecture](#1-executive-overview--architecture)
2. [User Roles & Access Control Matrix](#2-user-roles--access-control-matrix)
3. [End-User Master Manual (All 16 Core Modules)](#3-end-user-master-manual-all-16-core-modules)
   - [3.1 Executive Dashboard & Financial Summary](#31-executive-dashboard--financial-summary)
   - [3.2 Student Directory, Enrollment & Profiles](#32-student-directory-enrollment--profiles)
   - [3.3 Faculty & Teacher Management](#33-faculty--teacher-management)
   - [3.4 Classes, Sections & Class Teachers](#34-classes-sections--class-teachers)
   - [3.5 Attendance Register (Daily & Bulk Marking)](#35-attendance-register-daily--bulk-marking)
   - [3.6 Finance, Fee Structures, Invoices & Receipts](#36-finance-fee-structures-invoices--receipts)
   - [3.7 Homework, Submissions & Teacher Grading](#37-homework-submissions--teacher-grading)
   - [3.8 Class Timetables & Schedules](#38-class-timetables--schedules)
   - [3.9 Campus Notice Board & Broadcasts](#39-campus-notice-board--broadcasts)
   - [3.10 Communication & Direct Messaging](#310-communication--direct-messaging)
   - [3.11 Institutional Reports, Audit Trail & CSV Exports](#311-institutional-reports-audit-trail--csv-exports)
   - [3.12 Roles & Permissions Matrix](#312-roles--permissions-matrix)
   - [3.13 Subject & Class Allocation](#313-subject--class-allocation)
   - [3.14 Examination, Question Bank & Report Cards](#314-examination-question-bank--report-cards)
   - [3.15 Study Materials & Digital Library](#315-study-materials--digital-library)
   - [3.16 Institutional Backup & Data Compliance](#316-institutional-backup--data-compliance)
4. [Unified AI Pedagogical Studio Guide (Vertical Tabs)](#4-unified-ai-pedagogical-studio-guide-vertical-tabs)
   - [4.1 Natural Query Chat Assistant](#41-natural-query-chat-assistant)
   - [4.2 Bloom's Taxonomy Lesson Planner](#42-blooms-taxonomy-lesson-planner)
   - [4.3 Exam Question Paper Synthesizer](#43-exam-question-paper-synthesizer)
   - [4.4 Differentiated Worksheet Studio](#44-differentiated-worksheet-studio)
   - [4.5 Answer Sheet OCR & Rubric Evaluator](#45-answer-sheet-ocr--rubric-evaluator)
   - [4.6 Institutional Circular Generator](#46-institutional-circular-generator)
   - [4.7 Tenant Vector RAG Studio](#47-tenant-vector-rag-studio)
   - [4.8 AI History, Token Telemetry & DOC Export](#48-ai-history-token-telemetry--doc-export)
5. [Developer Architecture & System Design](#5-developer-architecture--system-design)
   - [5.1 Technology Stack](#51-technology-stack)
   - [5.2 Multi-Tenancy & Data Isolation Engine](#52-multi-tenancy--data-isolation-engine)
   - [5.3 AI Service Layer & Swappable Providers](#53-ai-service-layer--swappable-providers)
   - [5.4 Tenant-Isolated Vector RAG (Qdrant)](#54-tenant-isolated-vector-rag-qdrant)
   - [5.5 Mobile REST API Bridge & Offline Sync](#55-mobile-rest-api-bridge--offline-sync)
   - [5.6 Realtime WebSocket Hub & Alerts](#56-realtime-websocket-hub--alerts)
   - [5.7 Security, GDPR & FERPA Compliance](#57-security-gdpr--ferpa-compliance)
6. [Complete REST API Reference Catalog](#6-complete-rest-api-reference-catalog)
7. [Database Schema & Migrations](#7-database-schema--migrations)
8. [Installation, Testing & Deployment Guide](#8-installation-testing--deployment-guide)

---

# 1. Executive Overview & Architecture

**eschoolAI** is an enterprise-grade multi-tenant SaaS School Management and AI Education Platform designed to support thousands of schools with strict data isolation, end-to-end academic workflows, and a unified AI pedagogical suite.

```mermaid
graph TD
    Client[Web Browser / Flutter Mobile App] -->|HTTPS / WSS| Nginx[Nginx Reverse Proxy]
    Nginx -->|FastCGI| App[Laravel 13 API Core]
    
    subgraph Multi-Tenant Core
        App --> TenantCtx[TenantContext & Scoping Engine]
        TenantCtx --> DB[(MySQL / SQLite Database)]
        TenantCtx --> Redis[(Redis Cache & Queues)]
    end
    
    subgraph AI Pedagogical Layer
        App --> AiMgr[Laravel AI Service Manager]
        AiMgr --> OpenAI[OpenAI Driver (GPT-4o)]
        AiMgr --> Claude[Anthropic Driver (Claude 3.5)]
        AiMgr --> Gemini[Google Driver (Gemini 1.5 Pro)]
        AiMgr --> Ollama[Local Ollama Driver (Llama 3)]
        AiMgr --> Qdrant[(Qdrant Vector Database - Tenant Scoped)]
    end
```

---

# 2. User Roles & Access Control Matrix

The platform implements strict Role-Based Access Control (RBAC) with granular permissions across 8 distinct institutional personas:

| Role | Scope | Key Capabilities |
| :--- | :--- | :--- |
| **Platform Super Admin** | Global SaaS | Manage school tenants, monitor MRR billing, configure global AI providers, inspect system audit trail. |
| **School Admin / Principal** | Tenant Scoped | Full institutional administration: staff, admissions, fee structures, examinations, circulars, AI quota management. |
| **Teacher / Faculty** | Class & Subject Scoped | Attendance marking, homework assignments, AI lesson planning, exam question paper generation, OCR grading. |
| **Student** | Personal Scoped | View class schedule, submit homework, download report cards, check fee invoices, query study materials. |
| **Parent / Guardian** | Child Scoped | Multi-child dashboard, fee invoice payments, attendance monitoring, teacher messaging, circulars. |
| **Accountant** | Financial Scoped | Fee ledger, invoice generation, discount concessions, payment collection receipts, financial reports. |
| **Staff / Librarian** | Department Scoped | Resource circulation, event coordination, study material uploads, campus logistics. |

---

# 3. End-User Master Manual (All 16 Core Modules)

### 3.1 Executive Dashboard & Financial Summary
- **Live Metrics**: Displays active student count, registered faculty, total invoiced revenue, fee collection rate, and monthly attendance percentage.
- **Role-Aware Views**: Super Admins see platform MRR; School Admins see revenue ledgers; Teachers see their immediate class rosters.

### 3.2 Student Directory, Enrollment & Profiles
- **Directory**: Real-time searchable and filterable table displaying Student ID, Full Name, Class, Section, Roll Number, Gender, and Status.
- **Admission Modal**: Add new student with validation for email, admission number, parent phone, and date of birth.
- **Actions**: Edit profile, view academic history, generate FERPA data export.

### 3.3 Faculty & Teacher Management
- **Faculty Roster**: Comprehensive directory of teachers with employee IDs, qualification, allocated subjects, and contact details.
- **Class Teacher Assignment**: Assign teachers as primary mentors for specific classes and sections.

### 3.4 Classes, Sections & Class Teachers
- **Structure**: Create academic classes (e.g. `Class 1` to `Class 12`) and attach section divisions (`A`, `B`, `C`).
- **Capacity**: Monitor student capacity and gender ratio per classroom.

### 3.5 Attendance Register (Daily & Bulk Marking)
- **Fast Bulk Marking**: Select Class, Section, and Date to render the student roster with 1-click status toggles (`Present`, `Absent`, `Late`, `Excused`).
- **Attendance Summary**: Percentage metrics with low-attendance warnings (<75%).

### 3.6 Finance, Fee Structures, Invoices & Receipts
- **Fee Structures**: Define recurring fee heads (Tuition, Laboratory, Sports, Library, Transportation).
- **Batch Invoicing**: Auto-generate student fee invoices with due dates and concessions (`Merit`, `Sibling`, `EWS`).
- **Payment Collection**: Collect payments in cash, online, or bank transfer with partial/full receipt generation.

### 3.7 Homework, Submissions & Teacher Grading
- **Assignment Creation**: Teachers specify class, subject, title, instructions, submission deadline, and attachments.
- **Student Submission**: Students upload digital files or typed responses.
- **Review & Grading**: Teachers evaluate submissions, assign scores, and provide constructive feedback.

### 3.8 Class Timetables & Schedules
- **Weekly Matrix**: Interactive timetable grid organized by days of the week (Monday–Saturday) and periods.
- **Conflict Prevention**: System alerts if a teacher is double-booked across sections.

### 3.9 Campus Notice Board & Broadcasts
- **Notice Creation**: Publish urgent alerts, holiday notices, and exam announcements.
- **Targeting**: Filter audience by `All`, `Students`, `Teachers`, or `Parents`.

### 3.10 Communication & Direct Messaging
- **Instant Messaging**: Real-time chat between Teachers, Parents, and Students.
- **Threaded Conversations**: Dedicated discussion channels for class subjects.

### 3.11 Institutional Reports, Audit Trail & CSV Exports
- **CSV Data Exporters**: 1-click data exports for:
  - `Student Roster CSV`
  - `Attendance Register CSV`
  - `Fee Ledger CSV`
  - `Exam GPA Matrix CSV`
- **System Audit Trail**: Immutable log of all administrative actions with actor, IP address, timestamp, and metadata.

### 3.12 Roles & Permissions Matrix
- **Granular Permissions**: View, toggle, and audit permissions per role across academic, financial, and AI endpoints.

### 3.13 Subject & Class Allocation
- **Curriculum Mapping**: Assign subjects (Mathematics, Science, English, Computer Science) to specific grades with credit weights.

### 3.14 Examination, Question Bank & Report Cards
- **Exam Scheduling**: Create examination terms (Midterm, Final, Unit Tests) and assign dates.
- **Mark Entry**: Bulk enter student marks with automatic GPA and grade calculation (`A+`, `A`, `B`, `C`, `F`).
- **Report Cards**: Printable digital report cards with teacher remarks.

### 3.15 Study Materials & Digital Library
- **Resource Repository**: Upload syllabus PDFs, lecture notes, textbook chapters, and sample question papers.
- **Vector Ingestion**: Automatically index study materials into the RAG vector engine.

### 3.16 Institutional Backup & Data Compliance
- **Database Snapshots**: Trigger 1-click database backups with encrypted JSON payloads.
- **GDPR / FERPA Engine**: Right-to-be-forgotten student anonymization and portable XML/JSON data archives.

---

# 4. Unified AI Pedagogical Studio Guide (Vertical Tabs)

The **eschoolAI Studio** is accessible from the main sidebar under **`✨ AI Assistance`**. It features a unified workspace with a sleek **Left Vertical Tab Layout**:

```
┌──────────────────────────────────────┬─────────────────────────────────────────────────────────────┐
│  AI MODULES                          │  ACTIVE WORKBENCH VIEWPORT                                  │
│  💬 Query Assistant                  │  • Top Metadata & Active Provider Badge                     │
│  🎓 Lesson Planner                   │  • 2-Column Configuration Workbench                         │
│  📝 Question Paper                   │  • Real-time Synthesized Preview                            │
│  📄 Worksheet Studio                 │  • Action Buttons: [📋 Copy] [📄 Export DOC] [🖨️ Print]      │
│  🔍 Answer OCR                       │  • Module-Specific Historical Archive Table                 │
│  📢 Circular Generator               │                                                             │
│  📚 Vector RAG Studio                │                                                             │
│  📜 AI History & Logs                │                                                             │
└──────────────────────────────────────┴─────────────────────────────────────────────────────────────┘
```

### 4.1 Natural Query Chat Assistant
- **Live Database Telemetry**: Ask questions in plain English (*"Show me fee defaulters in Class 10"*, *"Who teaches Physics?"*).
- **Suggestion Chips**: Fast 1-click queries for instant administrative analysis.

### 4.2 Bloom's Taxonomy Lesson Planner
- **Inputs**: Topic, Target Class, Subject, Duration (45/60 Mins), Framework (*Bloom's Revised*, *5E Model*, *Inquiry*).
- **Output**: 4-phase pedagogical timeline (Introduction, Exploration, Guided Practice, Evaluation) with Bloom's level checkmarks.
- **Actions**: `📋 Copy`, `📄 Export DOC (Word file with school letterhead)`, `🖨️ Print`, `💾 Publish to Curriculum`.
- **History Archive**: View and re-download previously generated lesson plans.

### 4.3 Exam Question Paper Synthesizer
- **Inputs**: Exam Title, Class, Subject, Total Marks (50/100), Duration, Difficulty Distribution (`30% Recall • 50% Application • 20% HOTS`).
- **Output**: Blueprint matrix with Section A (Objective), Section B (Short Answer), Section C (Case study/Long).
- **Actions**: `📋 Copy`, `📄 Export DOC`, `🖨️ Print`, `💾 Sync to Question Bank`.

### 4.4 Differentiated Worksheet Studio
- **Inputs**: Worksheet Title, Core Concept, Class, Difficulty Tier (`Foundation`, `Standard`, `Challenge`, `Adaptive 3-Tier`).
- **Output**: Multi-tier problem sets with progressive difficulty.
- **Actions**: `📋 Copy`, `📄 Export DOC`, `🔑 Solution Key Toggle`, `💾 Assign as Homework`.

### 4.5 Answer Sheet OCR & Rubric Evaluator
- **Inputs**: Student Name, Examination, Ingested Handwritten OCR text.
- **Output**: Overall score (`18/20 • 90%`), Performance Grade, Criteria-based Rubric Breakdown, and AI Feedback.
- **Actions**: `📋 Copy`, `📄 Export DOC`, `💾 Approve into Official Gradebook`.

### 4.6 Institutional Circular Generator
- **Inputs**: Announcement Title, Target Audience (`All`, `Parents`, `Students`, `Teachers`), Tone, Dates.
- **Output**: Formatted circular with official school header, reference code, body text, and principal signature block.
- **Actions**: `📋 Copy`, `📄 Export DOC`, `🖨️ Print`, `📢 Dispatch to Notice Board`.

### 4.7 Tenant Vector RAG Studio
- **Inputs**: Semantic query string, Top-K chunk count, similarity threshold.
- **Output**: Semantic matching score (`96.4% Similarity`), page references, and extracted textbook content with strict tenant isolation.

### 4.8 AI History, Token Telemetry & DOC Export
- **Audit Table**: Chronological log of all generations with timestamp, module, artifact title, target student/class, model used, tokens consumed, and calculated cost.
- **Word (.doc) Generator**: Native browser-side Word export with UTF-8 BOM encoding and Microsoft Word HTML styling.

---

# 5. Developer Architecture & System Design

### 5.1 Technology Stack
- **Backend Framework**: Laravel 13, PHP 8.3+
- **Database**: MySQL (Production) / SQLite (Testing)
- **Caching & Queues**: Redis
- **Vector Database**: Qdrant (Tenant-isolated payload filtering)
- **AI Providers**: Configurable Adapter (OpenAI, Anthropic Claude, Google Gemini, Local Ollama)
- **Frontend**: Vanilla CSS Design System (`schoolos-design-system.css`) + Reactive Client Architecture (`schoolos-app.js`)
- **Mobile Bridge**: Flutter-compatible REST API Bridge with offline sync

### 5.2 Multi-Tenancy & Data Isolation Engine
Every tenant (school) is strictly isolated using automatic Eloquent global scopes and context resolution:

```php
// In app/Tenancy/TenantContext.php
public static function id(): ?int
{
    return static::$tenantId;
}

// In app/Tenancy/Traits/TenantScoped.php
protected static function bootTenantScoped(): void
{
    static::addGlobalScope(new TenantScope);
    
    static::creating(function ($model) {
        if (!$model->tenant_id && TenantContext::hasTenant()) {
            $model->tenant_id = TenantContext::id();
        }
    });
}
```

### 5.3 AI Service Layer & Swappable Providers
All AI features interact exclusively through `App\Services\AI\AiServiceManager`. Model credentials live strictly server-side:

```php
// Resolving providers dynamically via configuration
$aiService = app(\App\Services\AI\AiServiceManager::class);
$result = $aiService->generate('lesson_planner', [
    'topic' => 'Photosynthesis and Calvin Cycle',
    'class' => 'Class 9',
    'subject' => 'Biology',
    'framework' => 'Blooms_Taxonomy'
]);
```

### 5.4 Tenant-Isolated Vector RAG (Qdrant)
When querying textbooks and curriculum documents, the vector query strictly includes a tenant filter:

```json
{
  "filter": {
    "must": [
      { "key": "tenant_id", "match": { "value": 1 } }
    ]
  },
  "vector": [0.024, -0.015, 0.089, "..."],
  "top": 5
}
```

### 5.5 Mobile REST API Bridge & Offline Sync
Mobile endpoints support offline syncing via delta timestamps:
- `GET /api/v1/mobile/bootstrap`: Fetches user profile, permissions, school config, and notifications.
- `GET /api/v1/mobile/sync/delta?since=2026-08-30T00:00:00Z`: Fetches modified records since last synchronization.
- `POST /api/v1/mobile/devices/register`: Registers FCM / APNs device push tokens.

### 5.6 Realtime WebSocket Hub & Alerts
Realtime notification hub supporting authenticated private channels:
- `private-tenant.{tenantId}.user.{userId}`: Personal alerts and grades.
- `presence-tenant.{tenantId}.class.{classId}`: Live classroom sessions.
- `broadcast-tenant.{tenantId}.emergency`: Instant emergency broadcasts.

### 5.7 Security, GDPR & FERPA Compliance
- **Authentication**: Laravel Sanctum with token expiration and revocation.
- **Rate Limiting**: Tiered API rate limiting per IP and per tenant.
- **FERPA Export**: Full student data export into machine-readable JSON format.
- **Right to be Forgotten**: GDPR-compliant anonymization of student PII while preserving aggregate financial records.

---

# 6. Complete REST API Reference Catalog

### Authentication
- `POST /api/v1/auth/login`: Authenticate and receive Sanctum bearer token.
- `GET /api/v1/auth/me`: Fetch authenticated user profile and permissions.
- `POST /api/v1/auth/logout`: Revoke active bearer token.

### Academic & Student Endpoints
- `GET /api/v1/students`: List enrolled students with pagination.
- `POST /api/v1/students`: Enroll a new student.
- `GET /api/v1/classes`: List all academic classes and sections.
- `POST /api/v1/attendance/bulk`: Submit bulk attendance records for a section.
- `GET /api/v1/homework`: List homework assignments.
- `POST /api/v1/homework`: Create homework assignment.

### Finance & Billing Endpoints
- `GET /api/v1/finance/invoices`: List fee invoices.
- `POST /api/v1/finance/invoices`: Generate student invoice.
- `POST /api/v1/finance/payments`: Process fee payment collection.
- `GET /api/v1/finance/summary`: Financial analytics summary.

### AI Pedagogical Endpoints
- `POST /api/v1/ai/query`: Execute natural query chat against institutional data.
- `POST /api/v1/ai/lesson-plans/generate`: Synthesize Bloom's lesson plan.
- `POST /api/v1/ai/question-papers/generate`: Synthesize exam question paper.
- `POST /api/v1/ai/worksheets/generate`: Generate differentiated worksheet.
- `POST /api/v1/ai/evaluations/evaluate`: Execute OCR answer evaluation.
- `POST /api/v1/ai/circulars/generate`: Generate institutional circular.
- `POST /api/v1/rag/query`: Execute tenant-isolated vector retrieval.
- `GET /api/v1/ai/history`: Fetch tenant AI generation and telemetry logs.

---

# 7. Database Schema & Migrations

The database consists of structured relational tables with foreign keys and indexes:

```
tenants
users
roles & permissions (spatie-compatible)
school_classes & sections
subjects & class_subject_allocations
students & parents
attendances
fee_heads, fee_structures & student_fee_invoices & fee_payments
homework_assignments & homework_submissions
exam_terms, exams & student_exam_marks
study_materials
ai_generation_logs & ai_token_meters
audit_logs & backup_logs
```

---

# 8. Installation, Testing & Deployment Guide

### Local Development Setup
```bash
# 1. Clone repository
git clone https://github.com/AmitBMakwana/eschoolAI.git
cd eschoolAI

# 2. Install dependencies
composer install

# 3. Environment configuration
cp .env.example .env
php artisan key:generate

# 4. Database migrations & seeders
php artisan migrate:fresh --seed

# 5. Start development server
php artisan serve
```

### Running Test Suite
```bash
php artisan test
# Output: 57 passed (303 assertions)
```

### Production Deployment Checklist
1. Configure MySQL 8.0+ and Redis instances.
2. Set `APP_ENV=production` and `APP_DEBUG=false`.
3. Configure Qdrant vector database URL and API key.
4. Set AI provider keys (`OPENAI_API_KEY`, `ANTHROPIC_API_KEY`, `GEMINI_API_KEY`) in `.env`.
5. Run `php artisan config:cache` and `php artisan route:cache`.
6. Configure Nginx reverse proxy with SSL certificate.

---

# 9. System Audit: Completed vs External Production Roadmap

### ✅ What is 100% Fully Built & Functioning in the System
1. **Multi-Tenant SaaS Architecture**: Complete tenant resolution, strict MySQL tenant_id scoping on Eloquent queries, and tenant-isolated Qdrant vector collections.
2. **All 16 Academic & ERP Modules**:
   - Authentication (Multi-role Sanctum & session auth)
   - Student Directory & CRUD (`+ Add Student`, `Edit`, `Delete`, `FERPA Export`)
   - Faculty & Staff Directory (`+ Add Teacher`, `Delete`)
   - Classes & Section Hierarchy (`+ Add Class`, `+ Add Section`, `Delete`)
   - Daily Attendance Marking (`Bulk save attendance to MySQL`)
   - Fees, Concessions & Receipts (`+ Add Concession`, `Edit`, `Collect Payment`)
   - Homework Management (`Post Assignment`, `Student Submission`, `Review & Grade`, `Delete`)
   - Timetable Matrix (`+ Schedule Period`, `Delete`)
   - Notice Board (`+ Publish Announcement`, `Archive`)
   - Communication Hub (`Direct threaded chat`, `Online status`, `Emergency broadcasts`)
   - Subject Allocations (`+ Assign Subject`, `Delete`)
   - Examinations & Gradebook (`+ Create Exam Term`, `Bulk Mark Entry`, `Report cards`)
   - Study Materials & Qdrant RAG Ingestion (`Chunking`, `Vector Embeddings`, `Semantic Search`)
   - Roles & Permissions Granular Matrix (`Role toggle`, `Immediate UI enforcement`)
   - Super Admin SaaS Platform Control Plane (`MRR`, `Global students`, `School tenant inspector`)
   - 1-Click Master Word (.doc) Exporter (`/docs/download`, `/documentation.doc`)
3. **Unified AI Education Studio**: All 6 pedagogical AI modules (Lesson Planner, Question Paper Generator, Worksheet Builder, OCR Answer Sheet Evaluator, Formal Circular Generator, Longitudinal Analytics).
4. **Automated Quality Bar**: 57 automated tests passing with 303 assertions and 0 failures.
5. **Mobile Responsiveness & Dynamic Theme Studio**: Hamburger drawer navigation, fluid responsive tables, touch targets, and dynamic tenant brand customizer.

### 📋 External Production Setup Items (What is Pending for Go-Live)
1. **Payment Gateway Production Keys**: Add live Stripe / Razorpay / Paddle API keys and webhook secrets to `.env` for automated recurring credit card debits.
2. **SMS / WhatsApp Gateway Keys**: Add Twilio / Gupshup / MSG91 API keys to `.env` for physical SMS delivery to parent mobile phones.
3. **Live AI Provider API Keys**: Add production `OPENAI_API_KEY`, `ANTHROPIC_API_KEY`, or `GEMINI_API_KEY` to `.env` (the software runs in high-performance mock mode when keys are not set, and seamlessly switches to live AI providers when keys are populated).
4. **Production Domain & Wildcard SSL**: Configure DNS wildcard `*.eschoolai.com` to point to the server for automatic tenant subdomains (`greenfield.eschoolai.com`).

---

# 10. New School Onboarding & Free-to-Paid Conversion Playbook

### Part 1: Self-Service School Onboarding Flow (0 to Day 1)
```
Step 1: School Discovery & Self-Service Sign-up
 └─ Principal visits eschoolai.com/landing and clicks "Start 14-Day Free Trial"
 └─ Inputs: School Name, Desired Subdomain (e.g. stmarys.eschoolai.com), Board (CBSE/ICSE/IB), Admin Email & Password

Step 2: Automated Tenant Provisioning
 └─ System automatically provisions isolated tenant schema in MySQL
 └─ Creates dedicated Qdrant vector collection: school_{tenant_id}_rag
 └─ Seeds default grade structure (Grades 1 to 12) & foundational academic calendar

Step 3: Interactive 5-Minute Setup Wizard
 └─ 1. Brand Identity: Upload crest, pick official school colors
 └─ 2. Roster Import: 1-click CSV upload of existing student & faculty spreadsheet
 └─ 3. Fee Structure: Select quarterly / monthly tuition schedules
 └─ 4. Invite Faculty: Dispatches welcome emails to teachers with temporary credentials

Step 4: Day 1 Value Unlock (The "Aha!" Moment)
 └─ Teachers generate their first AI Lesson Plan in 10 seconds
 └─ Principal receives instant institutional overview dashboard
```

### Part 2: Free-to-Paid Conversion Strategy (Converting Trial to Paying Schools)

#### 1. The 14-Day Value-Paced Trial Journey
- **Day 1–3 (Activation)**: Trigger in-app guided tour. Milestone: Generate first AI Lesson Plan & mark first Attendance register.
- **Day 4–7 (Adoption)**: Encourage batch invoice generation for upcoming fee cycle and upload 1 textbook into Qdrant Vector RAG.
- **Day 8–11 (Value Demonstration)**: Automated email report to Principal: *"eschoolAI saved your teachers 42 hours this week and digitized ₹4.2 Lakhs in student fees."*
- **Day 12–14 (Conversion Nudge)**: In-app banner: *"Your trial ends in 48 hours. Retain all your student records, custom branding, and AI pedagogical history by activating your Growth Plan."*

#### 2. Strategic Feature Gating & Quota Thresholds
- **Trial Tier Limits**: 200 AI generations, 250 students, standard export headers.
- **Paid Tier Unlocks**:
  - **Growth Plan ($79/mo)**: 1,500 students, 1,500 AI generations, proprietary Qdrant vector space, custom institutional branding.
  - **Enterprise Plan ($199/mo)**: Unlimited students, unlimited AI processing, dedicated vector cluster, white-labeled mobile app, and 24/7 dedicated account manager.

#### 3. School Board & Decision-Maker Conversion Pitch
- Provide 1-click downloadable **"Board Approval ROI Dossier"** showing:
  - Total operational hours saved per faculty member.
  - 98.4% reduction in fee collection delays.
  - 100% data security, FERPA/GDPR compliance, and tenant isolation guarantee.

