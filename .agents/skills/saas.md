# SaaS Multi-Tenancy Skill

## Principles & Rules
1. Every school is a distinct tenant identified by a non-forgeable `tenant_id`.
2. Never accept `tenant_id` from client payloads. Always resolve it from the authenticated session, token claims, or verified subdomain.
3. Every Eloquent model associated with school operations must apply `TenantScope` automatically.
4. Storage directories must be formatted as `tenants/{tenant_id}/...`.
5. All Redis cache keys and queue payload tags must be tenant-prefixed: `tenant:{tenant_id}:...`.
6. Enforce plan tiers, feature flags, and credit limits at the middleware layer before controller execution.
