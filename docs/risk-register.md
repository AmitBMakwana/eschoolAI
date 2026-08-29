# Risk Register & Mitigation Strategy

| Risk ID | Category | Risk Description | Severity | Likelihood | Mitigation Strategy |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **R-01** | Security | Cross-tenant data leakage via missing global scope or manipulated foreign keys | Critical | Medium | Automatic `TenantScope` applied on all models; DB-level and middleware tenant verification; dedicated `TenantIsolationTest` in CI pipeline. |
| **R-02** | Security | Cross-tenant vector retrieval in Qdrant | Critical | Low | Mandatory payload filter (`tenant_id = $tenantId`) embedded in every vector search query builder before transmission to Qdrant. |
| **R-03** | Reliability | LLM Provider rate limits, downtime, or pricing changes | High | Medium | `AIProviderInterface` enables seamless provider failover (e.g. Gemini -> OpenAI -> Claude -> Local Ollama) configured per tenant. |
| **R-04** | Performance | High latency during heavy PDF OCR and embedding generation | High | High | Asynchronous Redis queue workers with priority lanes (`high`, `default`, `low`) and chunked background processing. |
| **R-05** | Financial | AI credit over-consumption draining SaaS margins | High | Medium | Server-side credit meter and rate limiter enforced at middleware layer before dispatching LLM prompts; hard stop when quota is reached. |
| **R-06** | Accuracy | AI hallucination in examination grading or lesson planning | High | Medium | Grounded RAG with strict top-K textbook citations; mandatory human-in-the-loop teacher review before marking answer sheets as final. |
| **R-07** | Network | WebSocket disconnections breaking live communication | Medium | Medium | Client automatically degrades gracefully to HTTP short-polling with exponential backoff on connection drops. |
