# RAG Architecture Skill

## Principles & Rules
1. Ingestion Pipeline:
   - Validate format (PDF, scanned PDF, image, docx).
   - Text extraction (pdfparser, OCR engine for images).
   - Text cleaning & normalization.
   - Semantic chunking (300-800 tokens with 10-15% overlap).
   - Embedding generation (1536d / 768d / 384d depending on active provider).
   - Indexing into Qdrant with tenant and academic metadata.
2. Query Pipeline:
   - Generate query embedding.
   - Mandatory Filter: `tenant_id` == current_tenant.
   - Academic Filters: `class_id`, `subject_id`, `chapter_id`.
   - Retrieve Top-K chunks (cosine similarity > threshold).
   - Construct grounded prompt with citations (Book, Chapter, Page).
   - Pass to LLM via `AIProviderInterface`.
3. Never send raw full books to LLM—only strict top-K grounded chunks.
