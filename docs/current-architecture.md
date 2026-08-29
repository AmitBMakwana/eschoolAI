# Current & Target Architecture Specification

## 1. High-Level Architecture Topology

```
+-------------------------------------------------------------------------+
|                         Clients & Presentation Layer                    |
|  +-----------------------------------+  +----------------------------+  |
|  |     Responsive Web Application    |  |    Flutter Mobile / Web    |  |
|  +-----------------+-----------------+  +--------------+-------------+  |
+--------------------|-----------------------------------|----------------+
                     | REST API (JSON)                   | REST & Realtime
                     v                                   v
+-------------------------------------------------------------------------+
|                            Laravel 13 Core Gateway                      |
|  +-------------------------------------------------------------------+  |
|  | Authentication & Unified Session Resolver (/login)                |  |
|  | Tenant Resolution Middleware (TenantContext & Global Scopes)      |  |
|  | RBAC & Permission Verification Gate                               |  |
|  | Subscription Quota & Rate Limit Validator                         |  |
|  +-------------------------------------------------------------------+  |
+--------------------+-----------------------------------+----------------+
                     |                                   |
                     v                                   v
+--------------------+--------------+   +----------------+----------------+
|      Transactional Datastore      |   |        AI & RAG Subsystem       |
|  +-----------------------------+  |   |  +---------------------------+  |
|  | MySQL 8.0                   |  |   |  | Laravel AI Service Layer  |  |
|  | (Strict Tenant-Scoped DB)   |  |   |  | (Swappable Provider API)  |  |
|  +-----------------------------+  |   |  +-------------+-------------+  |
|  | Redis 7.0                   |  |   |                |                |
|  | (Queues, Cache, PubSub)     |  |   |  +-------------v-------------+  |
|  +-----------------------------+  |   |  | RAG Orchestration Engine  |  |
|  | S3-Compatible Storage       |  |   |  | (OCR + Chunker + Embed)   |  |
|  | (tenant_{id}/files)         |  |   |  +-------------+-------------+  |
+-----------------------------------+   +----------------|----------------+
                                                         v
                                        +----------------+----------------+
                                        | Qdrant Vector Engine (Isolated) |
                                        +----------------+----------------+
                                                         |
                                        +----------------v----------------+
                                        | LLMs: Ollama / OpenAI / Claude  |
                                        +---------------------------------+
```

## 2. Key Architectural Tenets
1. **Unified Authentication Endpoint:** All users authenticate at `/api/v1/auth/login`. The server resolves tenant, user role, and permissions, returning appropriate JWT/Sanctum tokens and redirecting to role-specific dashboards.
2. **Strict Multi-Tenancy:** `tenant_id` is automatically injected and enforced on all queries via Eloquent global scopes.
3. **Provider-Swappable AI Layer:** `AIProviderInterface` decouples business logic from external LLM vendors.
4. **Tenant-Isolated RAG:** Document chunks in Qdrant are tagged and strictly queried by `tenant_id` and academic taxonomy (`class_id`, `subject_id`, `chapter_id`).
