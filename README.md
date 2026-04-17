# AgentForge

AgentForge is a multi-agent orchestration project scaffolded from the design document in `docs/superpowers/specs/2026-04-16-agentforge-design.md`.

## Implemented in this repo

- `agents/` runnable Python agent workers (`researcher`, `summarizer`, `reviewer`)
- `infra/docker-compose.yml` local stack for ElasticMQ, MinIO, DynamoDB local, n8n, orchestrator, and agent workers
- `infra/orchestrator/` queue-based role handoff service (supports per-task `agent_order` routing)
- `n8n/workflows/team-execution-flow.json` importable baseline n8n workflow export
- `n8n/workflows/retry-timeout-flow.json` and `n8n/workflows/notification-flow.json` for retry and notification orchestration
- `k8s/` deployment, configmap, HPA (KEDA-style), and Helm chart starter files
- `infra/terraform/` Terraform starter for SQS/S3/DynamoDB
- `dashboard/` working PHP API scaffold for Agent/Team/Task endpoints and n8n webhook handling

Dashboard persistence in the provided compose stack is configured to use DynamoDB Local.

Helm chart templates now render configmap, secret, per-agent deployments, and KEDA scaled objects.

## Local run

1. Start infrastructure and workers:

```bash
docker compose -f infra/docker-compose.yml up --build
```

2. Submit a sample task (from another shell):

```bash
python infra/submit_task.py
```

3. Inspect result queue messages (ElasticMQ UI/API) and container logs:

- ElasticMQ endpoint: `http://localhost:9324`
- n8n UI: `http://localhost:5678`
- MinIO console: `http://localhost:9001`
- DynamoDB local: `http://localhost:8000`
- Dashboard API: `http://localhost:8080`

## LLM provider configuration

Agent responses use `LLM_PROVIDER` from `agents/config.py`:

- `stub` (default): deterministic local responses (no key required)
- `openai`: uses `OPENAI_API_KEY` and `OPENAI_MODEL`
- `ollama`: uses local/server Ollama via `OLLAMA_BASE_URL` and `OLLAMA_MODEL`

If using `openai`, set `OPENAI_API_KEY` securely in environment (do not hardcode in repo files).

## Dashboard API quick check

Create an agent:

```bash
curl -X POST http://localhost:8080/api/agents \
  -H "Content-Type: application/json" \
  -d '{"name":"Research 1","role":"researcher","llm_model":"stub-v1","system_prompt":"Research thoroughly"}'
```

Create a team:

```bash
curl -X POST http://localhost:8080/api/teams \
  -H "Content-Type: application/json" \
  -d '{"name":"Default Team","agent_order":["researcher","summarizer","reviewer"]}'
```

Submit a task:

```bash
curl -X POST http://localhost:8080/api/tasks \
  -H "Content-Type: application/json" \
  -d '{"team_id":1,"input":"Summarize Kubernetes autoscaling strategies","max_retries":1}'
```

If an agent errors, orchestrator retries that role up to `max_retries`, then marks task `failed`.

Orchestrator queue selection is environment-driven (`TASK_QUEUE`, `AGENT_CHAT_QUEUE`, `RESULT_QUEUE`) with defaults matching the existing queue names.

Check status:

```bash
curl http://localhost:8080/api/tasks/<task_id>/status
```

Get conversation:

```bash
curl http://localhost:8080/api/tasks/<task_id>/conversation
```

Run orchestrator routing unit tests:

```bash
python -m unittest infra/orchestrator/tests/test_routing.py
```

## Next implementation milestones

1. Replace `dashboard/` PHP scaffold with full Laravel 11 app + Livewire UI.
2. Wire n8n workflow nodes to actual SQS queue polling and Laravel webhook callback.
3. Integrate real LLM providers (OpenAI/Ollama) in agents with configurable prompts.
4. Add persistence of task status/conversation to DynamoDB and artifacts to S3/MinIO.
5. Expand Helm chart templates and Terraform module structure for production-ready deployment.
