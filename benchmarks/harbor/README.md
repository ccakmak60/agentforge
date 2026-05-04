# AgentForge on Harbor / Terminal-Bench

This directory contains a Harbor external-agent adapter that lets you evaluate an AgentForge dashboard team against Harbor tasks, including Terminal-Bench 2.0.

## Prerequisites

1. Docker running locally.
2. Harbor installed:

   - `uv tool install harbor`
   - or `pip install harbor`

3. AgentForge stack running:

   - `docker compose -f infra/docker-compose.yml up --build -d`

4. A dashboard team created in `http://localhost:8080`.

## Smoke test Harbor itself

Run Harbor's oracle first:

`harbor run -d terminal-bench/terminal-bench-2 -a oracle -t adaptive-rejection-sampler`

If that succeeds, Harbor and Docker are configured.

## Run the local AgentForge smoke task

The local smoke task asks AgentForge to create `/tmp/agentforge-smoke.txt` inside the Harbor sandbox.

1. Export the dashboard team id:

   `export AGENTFORGE_TEAM_ID=1`

2. Run:

   `scripts/run_harbor_agentforge.sh`

Equivalent explicit command:

`harbor run -p benchmarks/harbor/tasks/agentforge-smoke -a agentforge-team --agent-import-path benchmarks/harbor/agentforge_harbor_agent.py`

## Run a Terminal-Bench task with AgentForge

Example:

`export AGENTFORGE_TEAM_ID=1`

`scripts/run_harbor_agentforge.sh terminal-bench/terminal-bench-2 -t adaptive-rejection-sampler`

You can add normal Harbor flags, for example:

`scripts/run_harbor_agentforge.sh terminal-bench/terminal-bench-2 -t adaptive-rejection-sampler -n 1`

## How the adapter works

`agentforge_harbor_agent.py` is a Harbor external agent.

For each Harbor task it:

1. Receives the task instruction from Harbor.
2. Submits the instruction to `POST /api/tasks` on the AgentForge dashboard using `AGENTFORGE_TEAM_ID`.
3. Polls AgentForge task status and conversation endpoints until completion.
4. Saves the final AgentForge response to `/tmp/agentforge_response.md` in the Harbor sandbox.
5. Extracts shell commands from the final answer.
6. Executes those commands in the Harbor sandbox through `environment.exec()`.

For best results, create a team whose final agent is instructed to return strict JSON:

`{"commands": ["shell command 1", "shell command 2"], "notes": "brief rationale"}`

## Environment variables

| Variable | Required | Default | Purpose |
|---|---:|---|---|
| `AGENTFORGE_TEAM_ID` | yes | none | Dashboard team id to evaluate |
| `AGENTFORGE_DASHBOARD_URL` | no | `http://localhost:8080` | Dashboard API base URL |
| `AGENTFORGE_MAX_POLLS` | no | `60` | Poll attempts before timeout |
| `AGENTFORGE_POLL_SECONDS` | no | `5` | Seconds between status polls |
| `AGENTFORGE_MAX_COMMANDS` | no | `8` | Max extracted commands to execute |

## Viewing results

Harbor writes jobs under `jobs/`.

Run:

`harbor view jobs`

Then open the Harbor viewer, usually:

`http://127.0.0.1:8080`

If AgentForge's dashboard is also using port `8080`, stop AgentForge or configure Harbor viewer to use a different port if supported by your Harbor version.
