# AgentForge — Multi-Agent Orchestration Platform

A portfolio project demonstrating PHP, Kubernetes, AWS, and n8n by building a platform where users define AI agent teams that collaborate on tasks via LLM-powered microservices.

## Architecture

```
┌─────────────────────────────────────────────────────┐
│                   PHP (Laravel)                      │
│              Admin Dashboard / API                   │
│   - Agent CRUD, team config, task submission         │
│   - Logs viewer, agent status dashboard              │
└──────────────┬──────────────────────┬───────────────┘
               │ REST API             │ Webhooks
               ▼                      ▼
┌──────────────────────┐   ┌─────────────────────────┐
│     AWS SQS          │   │       n8n                │
│  Agent Message Bus   │   │  Workflow Orchestrator   │
│  - task-queue        │   │  - Multi-agent flows     │
│  - result-queue      │   │  - Triggers & callbacks  │
│  - agent-chat        │   │  - Notification hooks    │
└──────────┬───────────┘   └────────────┬────────────┘
           │                            │
           ▼                            ▼
┌─────────────────────────────────────────────────────┐
│              Kubernetes (Minikube / EKS)             │
│                                                      │
│  ┌───────────┐ ┌───────────┐ ┌───────────┐         │
│  │ Agent Pod  │ │ Agent Pod  │ │ Agent Pod  │  ...   │
│  │ (Research) │ │ (Summarize)│ │ (Review)   │        │
│  │  Python    │ │  Python    │ │  Python    │        │
│  └───────────┘ └───────────┘ └───────────┘         │
│                                                      │
│  HPA: auto-scale based on SQS queue depth           │
└─────────────────────────────────────────────────────┘
               │
               ▼
┌──────────────────────┐   ┌──────────────────────┐
│     AWS S3           │   │    AWS DynamoDB       │
│  Agent artifacts     │   │  Agent state & logs   │
└──────────────────────┘   └──────────────────────┘
```

### Data Flow

1. User creates agents and a team via the Laravel dashboard
2. User submits a task → Laravel pushes to SQS `task-queue`
3. First agent pod picks up the task, processes it (LLM call), posts result to SQS `agent-chat`
4. n8n workflow listens on `agent-chat`, routes to next agent, handles retries/timeouts
5. Final result stored in S3/DynamoDB, webhook notifies Laravel dashboard

## Component 1: PHP Laravel Dashboard

Admin UI and REST API for managing agents, teams, and tasks.

### Pages

- **Agents** — CRUD: name, role (researcher/summarizer/reviewer), LLM model, system prompt, temperature
- **Teams** — Group agents into collaboration teams, define execution order
- **Tasks** — Submit a task to a team, view progress in real-time (polling SQS results)
- **Logs** — Stream agent conversation history from DynamoDB, filterable by team/task

### API Endpoints

- `POST /api/agents` — Create agent config
- `POST /api/teams` — Create team with ordered agent list
- `POST /api/tasks` — Submit task → pushes to SQS
- `GET /api/tasks/{id}/status` — Poll task progress
- `GET /api/tasks/{id}/conversation` — Full agent-to-agent chat log
- `POST /api/webhooks/n8n` — Webhook receiver for n8n results

### Tech

Laravel 11, Blade templates + Livewire for reactive UI, MySQL for config data.

## Component 2: Agent Pods (Python on Kubernetes)

Each agent type runs as a Python microservice in its own K8s pod.

### Agent Structure

- `BaseAgent` class: polls SQS, calls LLM, posts result back to SQS
- Each agent type overrides: system prompt, tools, output format
- LLM calls via LangChain (supports OpenAI API or local Ollama)

### Pre-built Agent Types

- **ResearchAgent** — Takes a topic, searches/reasons, produces findings
- **SummarizerAgent** — Takes findings, produces concise summary
- **ReviewerAgent** — Takes summary, critiques quality, requests revision or approves

### Kubernetes Resources

- `Deployment` per agent type (1 replica default)
- `HPA` scaling on custom metric: SQS queue depth (via KEDA)
- `ConfigMap` for agent config (model, prompt, temperature)
- `Secret` for API keys
- Helm chart: `helm install agentforge ./charts/agentforge`

### Container

Single `Dockerfile` for all agents, `AGENT_TYPE` env var selects behavior.

### Local Dev

Minikube with SQS replaced by ElasticMQ (SQS-compatible). Agents still run as pods.

## Component 3: AWS Services

All within free tier limits.

| Service    | Role                                             | Free Tier Limit          |
| ---------- | ------------------------------------------------ | ------------------------ |
| **SQS**    | Agent message bus (task-queue, agent-chat, result-queue) | 1M requests/month |
| **S3**     | Store agent artifacts (final outputs, uploaded docs)     | 5GB storage       |
| **DynamoDB** | Agent state, conversation logs, task status            | 25GB + 25 RCU/WCU |
| **ECR**    | Container image registry for agent pods                  | 500MB (private)   |

### Queue Design

- `task-queue` — Laravel pushes new tasks here
- `agent-chat` — Agents post messages for other agents; n8n routes them
- `result-queue` — Final results; Laravel polls or receives webhook

### Local Dev Substitutes

- **ElasticMQ** for SQS (Docker container, SQS-compatible API)
- **MinIO** for S3 (Docker container, S3-compatible API)
- **DynamoDB Local** (AWS-provided Docker image)

### Infrastructure as Code

Terraform files for provisioning SQS, S3, DynamoDB, ECR.

## Component 4: n8n Workflow Orchestrator

Manages multi-agent collaboration flows.

### Workflows

**1. Team Execution Flow (core)**

- Trigger: Webhook from Laravel when task submitted
- Steps:
  1. Read team config (agent order) from Laravel API
  2. Push task to first agent's SQS queue
  3. Wait for agent result (poll `agent-chat` queue)
  4. Pass output as input to next agent in chain
  5. Repeat until all agents complete
  6. Push final result to `result-queue`
  7. POST webhook to Laravel `/api/webhooks/n8n` with final output

**2. Retry & Timeout Flow**

- Agent doesn't respond within 60s → retry once → still no response → mark task failed → notify dashboard

**3. Notification Flow**

- On task completion → optional email/Slack notification via n8n built-in nodes

### Deployment

n8n runs as a Docker container (or K8s pod). Workflows exported as JSON in `n8n/workflows/` for reviewers to import and inspect.

## Project Structure

```
agentforge/
├── dashboard/              # PHP Laravel app
│   ├── app/
│   ├── routes/
│   ├── resources/views/
│   ├── docker-compose.yml
│   └── Dockerfile
├── agents/                 # Python agent microservices
│   ├── base_agent.py
│   ├── research_agent.py
│   ├── summarizer_agent.py
│   ├── reviewer_agent.py
│   ├── requirements.txt
│   └── Dockerfile
├── n8n/
│   └── workflows/          # Exported n8n workflow JSON
├── k8s/
│   ├── deployments/
│   ├── hpa/
│   ├── configmaps/
│   └── charts/agentforge/  # Helm chart
├── infra/
│   ├── terraform/          # AWS resource provisioning
│   ├── docker-compose.yml  # Local dev (ElasticMQ, MinIO, DynamoDB Local, n8n)
│   └── .env.example
└── README.md
```

## Success Criteria

- Recruiter can clone the repo, run `docker compose up`, and see the full system working locally
- Laravel dashboard shows agent creation, task submission, and live conversation logs
- Agents collaborate via SQS message passing orchestrated by n8n
- K8s manifests and Helm chart demonstrate production deployment readiness
- Terraform files demonstrate AWS infrastructure provisioning
- README includes architecture diagram, setup instructions, and demo walkthrough
