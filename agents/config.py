import os
from dataclasses import dataclass


@dataclass
class Settings:
    aws_region: str = os.getenv("AWS_REGION", "us-east-1")
    sqs_endpoint_url: str = os.getenv("SQS_ENDPOINT_URL", "http://elasticmq:9324")
    task_queue: str = os.getenv("TASK_QUEUE", "task-queue")
    agent_chat_queue: str = os.getenv("AGENT_CHAT_QUEUE", "agent-chat")
    result_queue: str = os.getenv("RESULT_QUEUE", "result-queue")
    poll_wait_seconds: int = int(os.getenv("POLL_WAIT_SECONDS", "10"))
    llm_provider: str = os.getenv("LLM_PROVIDER", "stub")
    openai_model: str = os.getenv("OPENAI_MODEL", "gpt-4o-mini")
    ollama_model: str = os.getenv("OLLAMA_MODEL", "llama3.1")
    openai_api_key: str = os.getenv("OPENAI_API_KEY", "")
    ollama_base_url: str = os.getenv("OLLAMA_BASE_URL", "http://host.docker.internal:11434")


settings = Settings()
