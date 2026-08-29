# Security Engineering Skill

## Principles & Rules
1. Zero-Trust Tenancy: Never trust client parameters for tenant ownership, user ID, student ID, or class ID.
2. Protection Layers:
   - CSRF & CORS policies tightly configured.
   - SQL Injection protection via Eloquent parameterized queries.
   - XSS sanitization on rich text inputs.
   - Mass-assignment protection via explicit `$fillable` model attributes.
   - Secure file uploads: MIME type validation, virus scan, random hashed filenames in private S3 buckets.
3. AI Guardrails:
   - Prompt injection defense: isolate untrusted inputs within delimiter blocks and enforce system prompt boundaries.
   - Encrypted secrets: Provider API keys encrypted at rest using AES-256-CBC.
   - Server-side rate limits & token budget ceilings.
