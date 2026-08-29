# Deployment & Infrastructure Architecture

## 1. Container Topology (`docker-compose.yml`)

```yaml
services:
  app:
    image: aischool-app:latest
    build:
      context: .
      dockerfile: Dockerfile
    environment:
      - APP_ENV=production
      - DB_CONNECTION=mysql
      - DB_HOST=db
      - REDIS_HOST=redis
      - QDRANT_HOST=qdrant
    depends_on:
      - db
      - redis
      - qdrant

  web:
    image: nginx:alpine
    ports:
      - "80:80"
      - "443:443"
    depends_on:
      - app

  db:
    image: mysql:8.0
    volumes:
      - mysql_data:/var/lib/mysql
    environment:
      - MYSQL_DATABASE=aischool_db
      - MYSQL_ROOT_PASSWORD=secret

  redis:
    image: redis:7-alpine
    volumes:
      - redis_data:/data

  qdrant:
    image: qdrant/qdrant:latest
    ports:
      - "6333:6333"
    volumes:
      - qdrant_data:/qdrant/storage

  queue-worker:
    image: aischool-app:latest
    command: php artisan queue:work redis --queue=high,default,low --sleep=3 --tries=3
    depends_on:
      - app
      - redis
```

## 2. Health Check Endpoints
- `GET /api/health` — Checks MySQL connectivity, Redis connectivity, Qdrant cluster status, and S3 bucket write permissions.
