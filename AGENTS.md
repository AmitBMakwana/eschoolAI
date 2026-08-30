# eschoolAI - Master Engineering Instructions

## PRODUCT
Build a production-ready multi-tenant SaaS School Management and AI Education Platform.
This is NOT a demo. This is NOT a static prototype. This is NOT a collection of disconnected screens.
Every feature must be functional end-to-end.
The application must be designed to support thousands of schools.

---

# CORE PRINCIPLES
1. Multi-tenant architecture from day one.
2. Strict tenant isolation.
3. API-first backend.
4. Responsive web application.
5. Flutter mobile/web compatibility.
6. Production-grade authentication.
7. Role-based permissions.
8. Secure file handling.
9. Queue-based heavy processing.
10. AI provider abstraction.
11. RAG with tenant isolation.
12. Qdrant vector database.
13. MySQL for transactional data.
14. Redis for cache and queues.
15. Object storage for files.
16. Comprehensive audit logs.
17. Automated tests.
18. Browser-based verification.
19. No fake functionality.
20. No hardcoded production data.
21. No exposing secrets to frontend.
22. No cross-tenant data leakage.

---

# STACK
- Backend: Laravel 13, PHP 8.3+
- Database: MySQL
- Cache/Queue: Redis
- Vector Database: Qdrant
- AI: Configurable provider architecture (Ollama, OpenAI-compatible, Anthropic-compatible, Gemini-compatible)
- OCR: Provider abstraction
- Storage: S3-compatible object storage
- Frontend: Responsive web application
- Mobile: Flutter-compatible REST API architecture
- Realtime: WebSocket/Socket.IO-compatible architecture
- Container: Docker
- Web server: Nginx

---

# TENANCY
Every school is a tenant.
Tenant-owned records MUST contain tenant_id.
Never trust tenant_id supplied by the frontend.
Resolve tenant from authenticated user/session/domain.
All queries must be tenant scoped.
All storage paths must be tenant scoped.
All vector searches must be tenant scoped.

---

# ROLES
- Platform Super Admin
- School Admin
- Principal
- Teacher
- Student
- Parent
- Accountant
- Staff

---

# SCHOOL MODULES
Authentication, School registration, OTP, Dashboard, Students, Teachers, Parents, Classes, Sections, Subjects, Attendance, Fees, Concessions, Homework, Assignments, Timetable, Notice Board, Communication, Reports, Finance, Exams, Questions, Results, Study Materials, Roles, Permissions, Settings.

---

# AI MODULES
AI Lesson Planner, Question Paper Generator, Worksheet Generator, Answer Sheet Evaluation, Circular Generator, Student Report Analysis.
All six sit behind one Laravel AI service layer with a swappable model provider (Gemini/Claude/OpenAI/Ollama), enforced usage limits, per-school cost tracking, and an ongoing prompt-improvement process.

---

# RAG
Supported sources: PDF, Scanned PDF, Images, Educational documents, Textbooks, Study materials.
Pipeline: Upload → Document validation → Storage → Text extraction → OCR if necessary → Cleaning → Chunking → Embeddings → Qdrant → Retrieval → Context filtering → Prompt construction → LLM → Structured output → Validation → Database → UI.
Never send an entire textbook to the LLM. Only send relevant retrieved chunks.

---

# AI SECURITY
Every RAG query MUST include tenant isolation.
Never allow one school to retrieve another school's content.
Never expose AI credentials to the frontend.
Implement rate limits, AI usage limits, AI request logging, and token/usage tracking.

---

# AI SERVICE LAYER
Build one Laravel AI service layer that all six AI modules call through — never let a feature call a model provider's SDK directly.
Requirements:
1. Provider-swappable: Gemini, Claude, OpenAI, and Ollama must all be usable behind the same interface, selectable per school or per environment via configuration, not code changes.
2. API security: provider API keys live server-side only, never in the frontend or Flutter app. Every AI endpoint requires authentication + tenant isolation + role/permission checks, exactly like every other API endpoint.
3. Usage limits: enforce per-school and per-role rate limits and quotas server-side (e.g. requests/minute, generations/day per plan tier). Never rely on the frontend to withhold a disabled button as the only control.
4. Usage and cost tracking: log every AI call with tenant_id, user_id, module, provider, model, input/output tokens, and computed cost. Aggregate this per school so Super Admin and School Admin can see real AI spend.
5. Prompt quality is a continuous process, not a one-time task: version prompts, keep a changelog of prompt revisions per module, and provide a way to compare output quality across versions.

---

# UI PRINCIPLES
The interface must feel like a modern premium SaaS.
Use any reference page only as visual inspiration, never copy branding or proprietary UI.
Prioritize: clarity, whitespace, strong hierarchy, simple navigation, low cognitive load, responsive layouts, accessible forms, clear status indicators, excellent empty/loading/error states, useful dashboards.

---

# DEVELOPMENT RULE
Before coding:
1. Inspect repository.
2. Understand existing code.
3. Identify reusable components.
4. Identify existing APIs.
5. Identify existing database schema.
6. Identify existing authentication.
7. Identify existing Flutter application.
8. Produce implementation plan.
9. Wait for approval where required.
10. Implement.
11. Test.
12. Browser verify.
13. Produce walkthrough artifact.
Never replace working functionality unnecessarily.
Never rewrite the existing application blindly.

---

# QUALITY BAR
Every completed feature must have: Database, Migration, Model, Validation, Authorization, Service, Controller, API, Tests, UI, Loading state, Empty state, Error state, Success state, Audit logging where appropriate, Documentation.

---

# DEFINITION OF DONE
A feature is not complete until:
- Backend works.
- API works.
- Database works.
- Authorization works.
- Tenant isolation works.
- UI works.
- Mobile-compatible API exists.
- Tests pass.
- Browser verification passes.
- No console errors.
- No obvious accessibility issues.
- No fake buttons.
- No placeholder functionality.
