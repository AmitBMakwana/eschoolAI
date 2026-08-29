# Security Architecture & Hardening Guide

## 1. Tenancy Isolation & Defense-in-Depth
1. **Model Layer:** All queries automatically scoped by `TenantScope`.
2. **Controller/Service Layer:** Explicit policy checks verifying `$record->tenant_id === TenantContext::id()`.
3. **Database Layer:** Foreign key constraints with cascading deletes tied to `tenant_id`.
4. **Qdrant Vector Layer:** Mandatory `Filter::must(FieldCondition::key('tenant_id')->match(TenantContext::id()))`.
5. **Storage Layer:** S3 file paths partitioned by tenant; signed URLs with short expiry (15 minutes).

## 2. API & Infrastructure Security
- **Authentication:** Laravel Sanctum token authentication with device fingerprinting and rate limiting.
- **CSRF & CORS:** Strict whitelisting of authorized tenant domains and Flutter mobile origins.
- **Input Sanitization:** Purifier applied to rich text inputs; strict schema validation on all API requests.
- **Secrets Management:** LLM API keys and payment gateway credentials encrypted at rest using AES-256-GCM.
- **Audit Logging:** Every administrative, billing, grading, and authentication event logged to immutable `audit_logs` table.
