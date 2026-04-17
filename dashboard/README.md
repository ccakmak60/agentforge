# Dashboard API (Implementation Scaffold)

This folder now contains a working PHP API scaffold for the dashboard endpoints from the design document.

## Implemented Endpoints

- `POST /api/agents`
- `POST /api/teams`
- `POST /api/tasks`
- `GET /api/tasks/{id}/status`
- `GET /api/tasks/{id}/conversation`
- `POST /api/webhooks/n8n`

## How it Works

- Data persistence supports two backends:
  - `json` (file-based in `dashboard/storage/*.json`)
  - `dynamodb` (DynamoDB API, typically `dynamodb-local` in compose)
- Task submission pushes to SQS-compatible `task-queue` (ElasticMQ in local dev).
- Status polling checks `result-queue` and updates task records.
- Webhook endpoint accepts final payloads from n8n.

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

Replace this scaffold with a full Laravel 11 + Livewire dashboard while keeping the same endpoint contract.
