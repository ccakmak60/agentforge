# Dashboard (UI + API Scaffold)

This folder contains a working PHP dashboard and API scaffold for the endpoints from the design document.

## Frontend

- `GET /` serves the AgentForge control room UI.
- The UI creates agents, creates teams, submits tasks, polls task status, and checks conversations through the API endpoints below.
- Styling follows the Linear-inspired design tokens in the attached design brief: pale surfaces, crisp borders, mono metadata, and no shadows.

## Implemented Endpoints

- `GET /api`
- `GET /api/agents`
- `GET /api/teams`
- `GET /api/tasks`
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
