from typing import Dict

from base_agent import BaseAgent
from config import settings
from llm_client import run_agent_with_skills


class SummarizerAgent(BaseAgent):
    role = "summarizer"

    def process(self, payload: Dict) -> str:
        conversation = payload.get("conversation", [])
        original_task = str(payload.get("input", ""))
        prior_output = (
            str(conversation[-1]["content"]) if conversation else "No prior agent output."
        )
        prompt = (
            f"Original user task:\n{original_task}\n\n"
            f"Prior agent output:\n{prior_output}\n\n"
            "Summarize the useful result for the original task. Do not invent a different task."
        )
        system_prompt = self.get_system_prompt(
            payload,
            "You are a summarizer agent. Produce a concise and clear summary.",
        )
        model = self.get_model(payload)
        skills = self.get_skills_index(payload)
        return run_agent_with_skills(
            provider=settings.llm_provider,
            model=model,
            system_prompt=system_prompt,
            user_prompt=prompt,
            skills=skills,
        )
