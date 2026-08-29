# Bootstrap Workflow

## Objective
Initialize the complete multi-tenant platform foundation, database migrations, base tenant resolver, and agent roles.

## Steps
1. Verify system prerequisites: PHP 8.3+, Composer, Node.js, MySQL, Redis, Qdrant.
2. Initialize Laravel 13 framework and configure `.env`.
3. Create tenant foundation migrations (`tenants`, `users`, `roles`, `permissions`, `subscriptions`).
4. Register `TenantScope` and `TenantMiddleware`.
5. Seed platform super admin and initial role matrices.
6. Verify initial database connectivity and tenant creation.
