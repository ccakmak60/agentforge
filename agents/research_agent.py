from typing import Dict

from base_agent import BaseAgent
from config import settings
from llm_client import run_agent_with_skills


class ResearchAgent(BaseAgent):
    role = "researcher"

    def process(self, payload: Dict) -> str:
        prompt = payload.get("input", "")
        system_prompt = self.get_system_prompt(
            payload,
            "You are a research agent. Produce concise evidence-focused findings.",
        )
        model = self.get_model(payload)
        skills = self.get_skills_index(payload)
        return run_agent_with_skills(
            provider=settings.llm_provider,
            model=model,
            system_prompt=system_prompt,
            user_prompt=str(prompt),
            skills=skills,
        )
