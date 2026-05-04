"""Harbor external agent adapter for AgentForge teams.

Usage:
    harbor run \
      -d terminal-bench/terminal-bench-2 \
      -a agentforge_harbor_agent:AgentForgeHarborAgent \
      --agent-import-path benchmarks/harbor/agentforge_harbor_agent.py \
      -t adaptive-rejection-sampler

Environment variables:
    AGENTFORGE_DASHBOARD_URL  Dashboard URL, default http://localhost:8080
    AGENTFORGE_AGENT_ID       Required agent id to evaluate
    AGENTFORGE_MAX_POLLS      Poll attempts, default 60
    AGENTFORGE_POLL_SECONDS   Seconds between polls, default 5
    AGENTFORGE_MAX_COMMANDS   Max commands to execute from response, default 8
"""

from __future__ import annotations

import asyncio
import json
import os
import re
import time
from typing import Any
from urllib.error import HTTPError
from urllib.request import Request, urlopen

from harbor.agents.base import BaseAgent
from harbor.environments.base import BaseEnvironment
from harbor.models.agent.context import AgentContext


class AgentForgeHarborAgent(BaseAgent):
    SUPPORTS_ATIF: bool = False

    @staticmethod
    def name() -> str:
        return "agentforge-team"

    def version(self) -> str | None:
        return "0.1.0"

    async def setup(self, environment: BaseEnvironment) -> None:
        self.dashboard_url = os.getenv(
            "AGENTFORGE_DASHBOARD_URL", "http://localhost:8080"
        ).rstrip("/")
        self.agent_id = int(os.environ["AGENTFORGE_AGENT_ID"])
        self.max_polls = int(os.getenv("AGENTFORGE_MAX_POLLS", "60"))
        self.poll_seconds = float(os.getenv("AGENTFORGE_POLL_SECONDS", "5"))
        self.max_commands = int(os.getenv("AGENTFORGE_MAX_COMMANDS", "8"))

    async def run(
        self,
        instruction: str,
        environment: BaseEnvironment,
        context: AgentContext,
    ) -> None:
        prompt = self._terminal_bench_prompt(instruction)
        task = await asyncio.to_thread(
            self._post_json,
            f"/api/agents/{self.agent_id}/tasks",
            {
                "input": prompt,
                "max_retries": 1,
            },
        )
        task_id = task["task_id"]
        self.logger.info(
            "Queued AgentForge task %s for agent %s", task_id, self.agent_id
        )

        result = await asyncio.to_thread(self._wait_for_result, task_id)
        conversation = result.get("conversation") or []
        final_content = ""
        if conversation:
            final_content = str(conversation[-1].get("content") or "")

        await environment.exec(
            self._write_file_command("/tmp/agentforge_response.md", final_content)
        )

        commands = self._extract_commands(final_content)
        executed = 0
        for command in commands[: self.max_commands]:
            self.logger.info("Executing AgentForge command: %s", command)
            exec_result = await environment.exec(command)
            executed += 1
            if getattr(exec_result, "return_code", 0) != 0:
                self.logger.warning(
                    "Command failed with return code %s",
                    getattr(exec_result, "return_code", None),
                )
                break

        if executed == 0:
            self.logger.warning(
                "AgentForge response did not include executable commands; saved response to /tmp/agentforge_response.md"
            )

    def _terminal_bench_prompt(self, instruction: str) -> str:
        return (
            "You are solving a Terminal-Bench task inside a Linux sandbox. "
            "Return ONLY a JSON object with this schema: "
            '{"commands": ["shell command 1", "shell command 2"], "notes": "short rationale"}. '
            "Commands must be non-interactive, safe for the task sandbox, and sufficient to complete the task. "
            "Do not wrap the JSON in markdown.\n\n"
            "Task instruction:\n"
            f"{instruction}"
        )

    def _post_json(self, path: str, payload: dict[str, Any]) -> dict[str, Any]:
        body = json.dumps(payload).encode("utf-8")
        request = Request(
            self.dashboard_url + path,
            data=body,
            headers={"Content-Type": "application/json"},
            method="POST",
        )
        return self._request_json(request)

    def _get_json(self, path: str) -> dict[str, Any]:
        return self._request_json(Request(self.dashboard_url + path))

    def _request_json(self, request: Request) -> dict[str, Any]:
        try:
            with urlopen(request, timeout=15) as response:
                return json.loads(response.read().decode("utf-8"))
        except HTTPError as exc:
            detail = exc.read().decode("utf-8", errors="replace")
            raise RuntimeError(
                f"AgentForge API request failed: HTTP {exc.code} {detail}"
            ) from exc

    def _wait_for_result(self, task_id: str) -> dict[str, Any]:
        for _ in range(self.max_polls):
            status = self._get_json(f"/api/tasks/{task_id}/status")
            if status.get("status") in {"completed", "failed"}:
                conversation = self._get_json(f"/api/tasks/{task_id}/conversation")
                if status.get("status") == "failed":
                    raise RuntimeError(f"AgentForge task failed: {conversation}")
                return conversation
            time.sleep(self.poll_seconds)
        raise TimeoutError(f"Timed out waiting for AgentForge task {task_id}")

    def _extract_commands(self, content: str) -> list[str]:
        stripped = content.strip()
        try:
            payload = json.loads(stripped)
            commands = payload.get("commands", [])
            if isinstance(commands, list):
                return [str(command) for command in commands if str(command).strip()]
        except json.JSONDecodeError:
            pass

        fenced = re.findall(
            r"```(?:bash|sh|shell)?\s*(.*?)```",
            content,
            flags=re.DOTALL | re.IGNORECASE,
        )
        if fenced:
            return [
                line.strip()
                for block in fenced
                for line in block.splitlines()
                if line.strip() and not line.strip().startswith("#")
            ]

        return [
            line.strip()
            for line in content.splitlines()
            if line.strip().startswith(
                (
                    "python",
                    "python3",
                    "bash",
                    "sh",
                    "make",
                    "pytest",
                    "sed",
                    "cat",
                    "echo",
                    "touch",
                    "mkdir",
                    "cp",
                    "mv",
                    "rm",
                )
            )
        ]

    def _write_file_command(self, path: str, content: str) -> str:
        quoted = json.dumps(content)
        return f"python3 - <<'PY'\nfrom pathlib import Path\nPath({path!r}).write_text({quoted})\nPY"
