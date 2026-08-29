# Laravel 13 Architecture Skill

## Principles & Rules
1. Adhere to strict layered architecture: Controller → FormRequest → Service → Repository (where justified) → Eloquent Model.
2. Use strongly-typed Form Requests for all input validation with sanitization.
3. Keep controllers thin; all business logic, transaction boundaries (`DB::transaction`), and event dispatches belong in Services.
4. Use standard JSON API response structures: `{ "success": true, "data": ..., "meta": ..., "message": "..." }`.
5. Error responses must be normalized: `{ "success": false, "error": { "code": "...", "message": "...", "details": ... } }`.
6. Enforce strict type hints and return types across all PHP 8.3+ code.
