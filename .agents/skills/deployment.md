# DevOps & Deployment Skill

## Principles & Rules
1. Containerized Services:
   - `app`: PHP 8.3 FPM + Composer + Node.
   - `web`: Nginx with optimized fastcgi and static asset caching.
   - `db`: MySQL 8.0 with automated migrations.
   - `redis`: Redis 7.0 for queues, cache, and pub/sub.
   - `qdrant`: Qdrant vector engine with persistent volume.
   - `queue-worker`: Supervisor managing `php artisan queue:work --queue=high,default,low`.
2. Health & Monitoring:
   - Endpoint `/api/health` checking DB, Redis, Qdrant, and Storage connectivity.
   - Graceful restart and rollback scripts.
