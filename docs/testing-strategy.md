# Testing Strategy & Quality Assurance Plan

## 1. Automated Test Suites

### 1.1 Tenant Isolation Tests (`tests/Feature/TenantIsolationTest.php`)
- Tests cross-tenant resource access attempts:
  - School A user attempts to fetch School B student records -> Expect HTTP 404 / 403.
  - School A admin attempts to query School B invoices -> Expect HTTP 403.
  - School A RAG query attempted against School B documents in Qdrant -> Expect 0 results returned.

### 1.2 Academic Core Feature Tests
- Class & Section creation, teacher allocation.
- Bulk attendance marking and percentage calculation.
- Fee structure, payment recording, and receipt generation.
- Timetable conflict validation.

### 1.3 AI & RAG Unit / Feature Tests
- Mocked LLM responses asserting schema adherence across all 6 modules.
- Token and cost calculation verification.
- Rate limiting and subscription quota cutoff validation.

## 2. Browser Verification Matrix
- End-to-End browser verification subagents running realistic user journeys:
  1. Super Admin onboarding a new school tenant.
  2. School Admin setting up academic years, classes, and fee structures.
  3. Teacher logging in, marking attendance, generating a Lesson Plan via RAG, and reviewing an AI-graded answer sheet.
  4. Parent switching between linked children and inspecting fee invoices.
  5. Student viewing timetable and homework assignments.
