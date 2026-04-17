from typing import Dict

from base_agent import BaseAgent
from llm_client import generate_with_settings


class ReviewerAgent(BaseAgent):
    role = "reviewer"

    def process(self, payload: Dict) -> str:
        conversation = payload.get("conversation", [])
        summary = conversation[-1]["content"] if conversation else ""
        system_prompt = "You are a reviewer agent. Critique quality and provide approval or revision feedback."
        return generate_with_settings(system_prompt=system_prompt, user_prompt=str(summary))
