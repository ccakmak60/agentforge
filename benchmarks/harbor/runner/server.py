from __future__ import annotations

import json
import os
import subprocess
import threading
import time
import uuid
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer
from pathlib import Path
from urllib.parse import parse_qs, urlparse

ROOT = Path(os.getenv("AGENTFORGE_ROOT", "/workspace"))
JOBS_DIR = Path(os.getenv("HARBOR_JOBS_DIR", "/workspace/jobs"))
DASHBOARD_URL = os.getenv("AGENTFORGE_DASHBOARD_URL", "http://dashboard")
RUNS: dict[str, dict] = {}
LOCK = threading.Lock()


def json_response(
    handler: BaseHTTPRequestHandler, payload: dict, status: int = 200
) -> None:
    body = json.dumps(payload, indent=2).encode("utf-8")
    handler.send_response(status)
    handler.send_header("Content-Type", "application/json")
    handler.send_header("Access-Control-Allow-Origin", "*")
    handler.send_header("Access-Control-Allow-Methods", "GET, POST, OPTIONS")
    handler.send_header("Access-Control-Allow-Headers", "Content-Type")
    handler.send_header("Content-Length", str(len(body)))
    handler.end_headers()
    handler.wfile.write(body)


def run_harbor(run_id: str, payload: dict) -> None:
    dataset = payload.get("dataset") or "benchmarks/harbor/tasks/agentforge-smoke"
    task = payload.get("task") or ""
    agent_id = str(payload.get("agent_id") or "")
    extra_args = payload.get("extra_args") or []

    run_dir = JOBS_DIR / "agentforge-ui" / run_id
    run_dir.mkdir(parents=True, exist_ok=True)
    stdout_path = run_dir / "stdout.log"
    stderr_path = run_dir / "stderr.log"
    config_path = run_dir / "harbor-config.json"

    harbor_config = {
        "job_name": f"agentforge-ui-{run_id}",
        "jobs_dir": str(JOBS_DIR),
        "n_concurrent_trials": int(payload.get("n_concurrent_trials") or 1),
        "environment": {"type": "docker"},
        "agents": [
            {
                "import_path": "benchmarks.harbor.agentforge_harbor_agent:AgentForgeHarborAgent",
                "env": {
                    "AGENTFORGE_DASHBOARD_URL": DASHBOARD_URL,
                    "AGENTFORGE_AGENT_ID": agent_id,
                },
            }
        ],
    }

    if str(dataset).startswith("benchmarks/") or Path(str(dataset)).exists():
        harbor_config["tasks"] = [{"path": str(dataset)}]
    else:
        dataset_name = str(dataset)
        dataset_config: dict[str, object] = {"name": dataset_name}
        if "@" in dataset_name:
            name, version = dataset_name.rsplit("@", 1)
            dataset_config = {"name": name, "version": version}
        if task:
            dataset_config["task_names"] = [str(task)]
        harbor_config["datasets"] = [dataset_config]

    config_path.write_text(json.dumps(harbor_config, indent=2))
    command = ["harbor", "run", "-c", str(config_path), "--yes"]
    if isinstance(extra_args, list):
        command += [str(arg) for arg in extra_args]

    (run_dir / "agentforge-run.json").write_text(
        json.dumps(
            {"command": command, "payload": payload, "harbor_config": harbor_config},
            indent=2,
        )
    )
    env = os.environ.copy()
    env["AGENTFORGE_DASHBOARD_URL"] = DASHBOARD_URL
    env["AGENTFORGE_AGENT_ID"] = agent_id
    env["HARBOR_JOBS_DIR"] = str(JOBS_DIR)

    with LOCK:
        RUNS[run_id].update(
            {
                "status": "running",
                "command": command,
                "run_dir": str(run_dir),
                "started_at": time.time(),
            }
        )

    try:
        with stdout_path.open("w") as stdout_file, stderr_path.open("w") as stderr_file:
            process = subprocess.run(
                command,
                cwd=str(ROOT),
                env=env,
                stdout=stdout_file,
                stderr=stderr_file,
                text=True,
                timeout=int(payload.get("timeout_sec") or 3600),
                check=False,
            )
        status = "completed" if process.returncode == 0 else "failed"
        with LOCK:
            RUNS[run_id].update(
                {
                    "status": status,
                    "return_code": process.returncode,
                    "finished_at": time.time(),
                }
            )
    except Exception as exc:
        stderr_path.write_text(str(exc))
        with LOCK:
            RUNS[run_id].update(
                {"status": "failed", "error": str(exc), "finished_at": time.time()}
            )


class Handler(BaseHTTPRequestHandler):
    def do_OPTIONS(self) -> None:
        json_response(self, {})

    def do_GET(self) -> None:
        parsed = urlparse(self.path)
        if parsed.path == "/health":
            json_response(self, {"status": "ok"})
            return
        if parsed.path == "/runs":
            with LOCK:
                runs = list(RUNS.values())
            json_response(self, {"runs": runs})
            return
        if parsed.path.startswith("/runs/"):
            run_id = parsed.path.split("/", 2)[2]
            with LOCK:
                run = RUNS.get(run_id)
            if run is None:
                json_response(self, {"error": "run not found"}, 404)
                return
            payload = dict(run)
            run_dir = Path(payload.get("run_dir") or "")
            stdout_path = run_dir / "stdout.log"
            stderr_path = run_dir / "stderr.log"
            payload["stdout_tail"] = (
                stdout_path.read_text(errors="replace")[-12000:]
                if stdout_path.exists()
                else ""
            )
            payload["stderr_tail"] = (
                stderr_path.read_text(errors="replace")[-12000:]
                if stderr_path.exists()
                else ""
            )
            json_response(self, payload)
            return
        json_response(self, {"error": "not found"}, 404)

    def do_POST(self) -> None:
        parsed = urlparse(self.path)
        if parsed.path != "/runs":
            json_response(self, {"error": "not found"}, 404)
            return
        length = int(self.headers.get("Content-Length") or "0")
        raw = self.rfile.read(length).decode("utf-8") if length else "{}"
        payload = json.loads(raw or "{}")
        if not payload.get("agent_id"):
            json_response(self, {"error": "agent_id is required"}, 422)
            return
        run_id = uuid.uuid4().hex[:12]
        with LOCK:
            RUNS[run_id] = {
                "id": run_id,
                "status": "queued",
                "created_at": time.time(),
                "payload": payload,
            }
        thread = threading.Thread(
            target=run_harbor, args=(run_id, payload), daemon=True
        )
        thread.start()
        json_response(self, RUNS[run_id], 202)

    def log_message(self, format: str, *args) -> None:
        print(f"{self.address_string()} - {format % args}")


if __name__ == "__main__":
    host = os.getenv("HARBOR_RUNNER_HOST", "0.0.0.0")
    port = int(os.getenv("HARBOR_RUNNER_PORT", "8090"))
    print(f"AgentForge Harbor runner listening on {host}:{port}")
    ThreadingHTTPServer((host, port), Handler).serve_forever()
