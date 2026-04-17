from typing import Dict

from base_agent import BaseAgent
from llm_client import generate_with_settings


class ResearchAgent(BaseAgent):
    role = "researcher"

    def process(self, payload: Dict) -> str:
        prompt = payload.get("input", "")
        system_prompt = "You are a research agent. Produce concise evidence-focused findings."
        return generate_with_settings(system_prompt=system_prompt, user_prompt=str(prompt))
