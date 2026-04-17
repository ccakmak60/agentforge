from typing import Dict

from base_agent import BaseAgent
from llm_client import generate_with_settings


class SummarizerAgent(BaseAgent):
    role = "summarizer"

    def process(self, payload: Dict) -> str:
        conversation = payload.get("conversation", [])
        prior = conversation[-1]["content"] if conversation else payload.get("input", "")
        system_prompt = "You are a summarizer agent. Produce a concise and clear summary."
        return generate_with_settings(system_prompt=system_prompt, user_prompt=str(prior))
