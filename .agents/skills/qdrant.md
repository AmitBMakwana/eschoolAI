# Qdrant Vector Database Skill

## Principles & Rules
1. Collection Architecture:
   - Primary Collection: `school_knowledge_base`.
   - Vector dimension: Configurable (default 1536 for OpenAI/Gemini or 768 for Ollama nomic-embed-text).
   - Distance metric: Cosine.
2. Payload Indexing:
   - Must index payload fields for fast filtered search: `tenant_id` (keyword), `class_id` (integer), `subject_id` (integer), `chapter_id` (integer), `document_id` (integer).
3. Hard Isolation:
   - All point upserts, queries, and deletions MUST include `tenant_id` filter conditions.
   - Cross-tenant queries are fatal security violations.
