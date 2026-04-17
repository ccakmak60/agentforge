import os

from research_agent import ResearchAgent
from reviewer_agent import ReviewerAgent
from summarizer_agent import SummarizerAgent


def build_agent():
    agent_type = os.getenv("AGENT_TYPE", "researcher").lower()
    if agent_type == "researcher":
        return ResearchAgent()
    if agent_type == "summarizer":
        return SummarizerAgent()
    if agent_type == "reviewer":
        return ReviewerAgent()
    raise ValueError(f"Unsupported AGENT_TYPE: {agent_type}")


if __name__ == "__main__":
    build_agent().run()
