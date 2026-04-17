# Dashboard API (Migration in Progress)

This folder contains the dashboard API implementation while migrating from the original single-file PHP scaffold toward a Laravel-style structure.

## Implemented Endpoints

- `POST /api/agents`
- `POST /api/teams`
- `POST /api/tasks`
- `GET /api/tasks/{id}/status`
- `GET /api/tasks/{id}/conversation`
- `POST /api/webhooks/n8n`

## Current Architecture

- `public/index.php` is now a thin entrypoint.
- `bootstrap/app.php` dispatches method + path to route handlers.
- `routes/api.php` maps endpoint contracts to controller classes under `app/Http/Controllers`.
- Existing store and SQS integrations in `src/` are reused during migration to preserve behavior.

### Storage backend env vars

- `STORAGE_BACKEND=json|dynamodb`
- `DYNAMODB_ENDPOINT=http://dynamodb-local:8000`
- `AWS_REGION=us-east-1`

## Local Run

Use root compose stack:

```bash
docker compose -f ../infra/docker-compose.yml up --build
```

API base URL:

- `http://localhost:8080`

## Planned Upgrade

Complete migration to a full Laravel 11 application + Livewire dashboard while keeping this API contract stable.
