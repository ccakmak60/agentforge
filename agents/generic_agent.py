import json
import logging
import time
from typing import Any, Dict

from base_agent import BaseAgent
from config import settings
from llm_client import run_agent_with_skills

logger = logging.getLogger(__name__)


class GenericAgent(BaseAgent):
    role = "skill"

    def _handle_message(self, message: Dict[str, Any]) -> None:
        payload = json.loads(message["Body"])
        target_role = str(payload.get("target_role") or self.role)

        logger.info(
            "%s agent processing dynamic role %s for task %s",
            self.role,
            target_role,
            payload.get("task_id"),
        )
        try:
            output = self.process(payload)
            payload.setdefault("conversation", []).append(
                {
                    "role": target_role,
                    "content": output,
                    "timestamp": int(time.time()),
                }
            )
            payload["completed_role"] = target_role
        except Exception as exc:
            payload["failed_role"] = target_role
            payload["error"] = str(exc)

        self.sqs.send_message(
            QueueUrl=self.agent_chat_queue_url, MessageBody=json.dumps(payload)
        )
        self.sqs.delete_message(
            QueueUrl=self.task_queue_url, ReceiptHandle=message["ReceiptHandle"]
        )

    def process(self, payload: Dict[str, Any]) -> str:
        target_role = str(payload.get("target_role") or self.role)
        conversation = payload.get("conversation", [])
        original_task = str(payload.get("input", ""))
        prior_output = (
            str(conversation[-1]["content"]) if conversation else "No prior agent output."
        )
        user_prompt = (
            f"Original user task:\n{original_task}\n\n"
            f"Prior agent output:\n{prior_output}\n\n"
            f"Your role is {target_role}. Continue the workflow by producing the next useful artifact for the original task. "
            "Do not invent a different task. Do not ask for missing content unless the original task is impossible to act on."
        )
        system_prompt = self.get_system_prompt(
            payload,
            f"You are an expert {target_role} agent. Complete the original user task from your role. Follow attached skill instructions when relevant.",
            role=target_role,
        )
        model = self.get_model(payload, role=target_role)
        skills = self.get_skills_index(payload)
        return run_agent_with_skills(
            provider=settings.llm_provider,
            model=model,
            system_prompt=system_prompt,
            user_prompt=user_prompt,
            skills=skills,
        )
