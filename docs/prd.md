# Product Requirements Document (PRD)

## 1. Product Summary
**AI SchoolOS** is a multi-tenant SaaS School Management and AI Education Platform designed for K-12 and higher educational institutions. It combines institutional management (admissions, attendance, fees, timetables, exams) with a state-of-the-art AI education layer grounded in curriculum textbooks.

---

## 2. Key Personas & Role Boundaries
1. **SaaS Super Admin:** Platform-wide oversight, subscription management, tenant onboarding, AI token/cost analytics.
2. **School Admin / Principal:** Full school operations, fee structuring, staff allocation, AI settings, circular publishing.
3. **Teacher:** Class attendance, homework assignment, exam creation, AI Lesson Planner, Question Paper Generator, Worksheet Generator, and Answer Sheet Evaluation.
4. **Student:** Academic schedule, homework submission, exam results, learning materials, and AI study assistant (school-controlled).
5. **Parent:** Multi-child academic monitoring, attendance alerts, online fee payments, and teacher communication.
6. **Accountant:** Fee collection, concession approvals, receipts, and finance ledgers.
7. **Staff:** Dedicated operational permissions (e.g. transport, library, reception).

---

## 3. The Six Core AI Modules
1. **AI Lesson Planner:** Generates structured pedagogical plans from class, subject, and chapter, with explicit citations from school-uploaded textbooks.
2. **Question Paper Generator:** Produces complete, print-ready exam papers with section breakdowns, marks allocations, Bloom's taxonomy balance, and standalone answer keys.
3. **Worksheet Generator:** Lightweight, daily practice worksheets covering specific topics with mixed question types.
4. **Answer Sheet Evaluation:** Ingests scanned handwritten student answer sheets via OCR, compares against RAG textbook context and answer rubrics, produces AI scores and feedback with mandatory teacher mark overrides.
5. **Circular Generator:** Drafts targeted administrative notices using school operational data (events, holidays, fee dues) with human approval before publishing.
6. **Student Report Analysis:** Generates rich narrative commentary on student performance by synthesizing attendance, marks, and homework metrics.
