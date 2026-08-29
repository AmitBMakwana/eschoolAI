# Backend Development Workflow

## Objective
Standardized workflow for delivering backend modules, migrations, controllers, services, and APIs.

## Steps
1. Define migration and model with `tenant_id` and `TenantScope`.
2. Implement FormRequest with validation and sanitization.
3. Write Service class encapsulating domain logic and database transactions.
4. Create Controller exposing REST endpoint under `/api/v1/`.
5. Write feature and unit tests (including cross-tenant attempt test).
6. Register route with `auth:sanctum` and `tenant` middleware.
7. Run `php artisan test` and confirm 100% pass rate.
