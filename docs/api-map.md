# API Specification & Routing Map

All API routes are prefixed under `/api/v1/` and return normalized JSON envelopes:
`{ "success": true, "data": ..., "message": "...", "meta": { "pagination": ... } }`

---

## 1. Authentication & Tenant Resolution
- `POST /api/v1/auth/login` — Unified login resolving tenant, user role, and permissions.
- `POST /api/v1/auth/logout` — Revokes current token/session.
- `POST /api/v1/auth/verify-otp` — Verify mobile or email OTP during signup/reset.
- `POST /api/v1/auth/forgot-password` — Trigger password reset workflow.
- `POST /api/v1/auth/reset-password` — Finalize password reset.
- `GET /api/v1/auth/me` — Current authenticated user profile with active role & school metadata.

---

## 2. Academics & School Operations
- `GET/POST /api/v1/classes` — List / Create classes.
- `GET/PUT/DELETE /api/v1/classes/{id}` — Manage specific class and associated sections.
- `GET/POST /api/v1/students` — Filterable student directory / Register student.
- `GET/PUT/DELETE /api/v1/students/{id}` — Student profile, academic history, dues.
- `GET/POST /api/v1/teachers` — Teacher directory / Add teacher with subject allocations.
- `POST /api/v1/attendance/mark` — Bulk attendance submission for a class/section.
- `GET /api/v1/attendance/summary` — Daily/monthly analytics by class.
- `GET/POST /api/v1/timetables` — Query and generate timetable matrices.
- `GET/POST /api/v1/homework` — List / Assign homework with attachments.
- `POST /api/v1/homework/{id}/submit` — Student submission endpoint.

---

## 3. Communication & Notices
- `GET/POST /api/v1/notices` — View notice board / Create targeted circular.
- `GET /api/v1/messages/threads` — List user chat conversations.
- `POST /api/v1/messages/send` — Send realtime message to peer/group.

---

## 4. Finance & Fee Management
- `GET/POST /api/v1/finance/fee-structures` — Manage fee heads and class fee plans.
- `GET /api/v1/finance/invoices` — Query student fee invoices and payment statuses.
- `POST /api/v1/finance/payments/collect` — Record fee payment and generate receipt.
- `GET /api/v1/finance/defaulters` — List students with overdue balances.
- `POST /api/v1/finance/concessions` — Apply discount/scholarship to student fees.

---

## 5. Examinations & Grading
- `GET/POST /api/v1/exams` — Manage exams and schedules.
- `GET/POST /api/v1/question-bank` — Browse and contribute to question repository.
- `POST /api/v1/exams/{id}/results` — Bulk marks entry and grade computation.
- `POST /api/v1/exams/{id}/publish` — Publish results to students/parents.

---

## 6. AI & RAG Subsystem
- `POST /api/v1/ai/knowledge/upload` — Upload textbook (PDF/Image) for ingestion.
- `GET /api/v1/ai/knowledge/status/{doc_id}` — Ingestion pipeline progress polling.
- `POST /api/v1/ai/lesson-plan/generate` — Generate structured lesson plan via RAG.
- `POST /api/v1/ai/question-paper/generate` — Generate print-ready exam paper & answer key.
- `POST /api/v1/ai/worksheet/generate` — Generate topic-level practice worksheets.
- `POST /api/v1/ai/answer-sheet/evaluate` — Submit scanned answer sheet for OCR & AI grading.
- `PUT /api/v1/ai/answer-sheet/{id}/override` — Teacher mark review and override.
- `POST /api/v1/ai/circular/generate` — Draft announcement using structured school context.
- `POST /api/v1/ai/student-report/generate` — Analyze performance trends for narrative report card.
- `GET /api/v1/ai/usage/metrics` — Query tenant AI credit consumption and cost metrics.
