# Multi-Tenant Architecture

## 1. Tenancy Model: Shared Database with Discriminator (`tenant_id`)

All institutional records in MySQL are tagged with a non-null foreign key `tenant_id`.

```
                    Incoming HTTP / WebSocket Request
                                   │
                                   ▼
                   ┌───────────────────────────────┐
                   │   TenantResolutionMiddleware   │
                   └───────────────┬───────────────┘
                                   │
                Resolves tenant from Token / Subdomain
                                   │
                                   ▼
                   ┌───────────────────────────────┐
                   │    TenantContext Singleton    │
                   └───────────────┬───────────────┘
                                   │
        ┌──────────────────────────┴──────────────────────────┐
        ▼                                                     ▼
┌───────────────────────────────┐             ┌───────────────────────────────┐
│     Eloquent Models Scope     │             │    Qdrant Vector Retrieval    │
│  (Global Scope: tenant_id)    │             │   (Must Filter: tenant_id)    │
└───────────────────────────────┘             └───────────────────────────────┘
```

## 2. Implementation Rules
1. **Tenant Identification:**
   - Evaluated via authenticated Sanctum token claim, authenticated session, or custom domain header.
   - User inputs specifying `tenant_id` are strictly rejected.
2. **Global Scoping:**
   - Every tenant-scoped model implements `TenantScoped` trait which automatically appends `where('tenant_id', TenantContext::id())` to all select, update, and delete queries.
   - Insertion events automatically stamp `tenant_id = TenantContext::id()`.
3. **Storage Isolation:**
   - All uploaded documents, textbooks, and answer sheets are placed under private object storage paths: `tenants/{tenant_id}/...`.
4. **Queue Isolation:**
   - Job payloads encapsulate `tenant_id`. When picked up by background workers, `TenantContext` is re-hydrated prior to executing the job.
