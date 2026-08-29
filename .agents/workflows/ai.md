# AI & RAG Integration Workflow

## Objective
Standardized workflow for building and verifying AI feature modules through the central AI Service Layer.

## Steps
1. Verify module requirements (e.g. Lesson Planner, Question Paper, Worksheet, Evaluation, Circular, Student Report).
2. For textbook-grounded modules: verify Qdrant ingestion and tenant filter.
3. For structured-data modules: prepare sanitized input context from MySQL performance records.
4. Route generation request through `AIServiceLayer` via `AIProviderInterface`.
5. Enforce token limits and quota checks before dispatching LLM request.
6. Parse and validate structured JSON output schema.
7. Log usage metrics (`tenant_id`, `user_id`, `tokens`, `cost`).
8. Return result to client with confidence indicators and edit capabilities.
