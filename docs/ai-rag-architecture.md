# AI & RAG Architecture Specification

## 1. Document Ingestion Pipeline

```
Textbook / Notes (PDF / Image)
              │
              ▼
    [ Document Upload ] ──> Store in S3 (tenants/{tenant_id}/docs/)
              │
              ▼
   [ Text Extraction Engine ] ──> OCR fallback for scanned images
              │
              ▼
   [ Semantic Text Chunker ] ──> 500-token chunks with 50-token overlap
              │
              ▼
  [ Embedding Vector Engine ] ──> Configurable (e.g. text-embedding-3-small)
              │
              ▼
  [ Qdrant Vector Datastore ] ──> Index with metadata payload:
                                  { tenant_id, class_id, subject_id, chapter_id, page }
```

## 2. RAG Query & Prompt Orchestration

```
Teacher Request (Class, Subject, Chapter, Learning Objectives)
              │
              ▼
   [ Generate Query Embedding ]
              │
              ▼
   [ Qdrant Search with Filters ]
      Filter: tenant_id == current_tenant
      Filter: class_id == target_class
      Filter: subject_id == target_subject
      Filter: chapter_id == target_chapter
              │
              ▼
   [ Retrieve Top-K Context Chunks ]
              │
              ▼
   [ Grounded Prompt Assembly ] ──> Strict delimiters + System instructions
              │
              ▼
   [ AI Service Layer Provider ] ──> Gemini / Claude / OpenAI / Ollama
              │
              ▼
   [ Output Parser & Validator ] ──> Ensure valid JSON schema
              │
              ▼
   [ Token & Cost Logger ] ──> Deduct tenant AI credit quota
              │
              ▼
   [ Deliver to UI Wizard ] ──> Editable sections with citation badges
```

## 3. Provider Abstraction Contract
```php
interface AIProviderInterface {
    public function generate(string $systemPrompt, string $userPrompt, array $options = []): string;
    public function generateStructured(string $systemPrompt, string $userPrompt, array $jsonSchema, array $options = []): array;
    public function embed(string|array $text): array;
}
```
Supported Drivers: `OpenAIProvider`, `AnthropicProvider`, `GeminiProvider`, `OllamaProvider`.
