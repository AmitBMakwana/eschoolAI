# Testing & Quality Assurance Skill

## Principles & Rules
1. Test Categories:
   - Unit Tests: Service logic, chunking algorithms, pricing calculations, token counters.
   - Feature Tests: API endpoints, validation errors, session handling.
   - Tenant Isolation Tests: Explicit cross-tenant access attempts (HTTP 403 / 404 expected).
   - RAG Verification: Ingestion mocks and vector retrieval accuracy tests.
   - Browser E2E Tests: Interactive login, wizard completion, attendance marking, export verification.
2. Definition of Done:
   - 100% passing tests.
   - Zero browser console errors.
   - Zero cross-tenant data leakage.
   - All edge cases (zero states, network drops, quota exhaustion) handled gracefully.
