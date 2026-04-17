from config import settings


def generate_response(provider: str, model: str, system_prompt: str, user_prompt: str) -> str:
    normalized = provider.lower().strip()

    if normalized == "stub":
        return f"[stub:{model}] {user_prompt}"

    if normalized == "openai":
        try:
            from langchain_openai import ChatOpenAI
        except Exception as exc:
            raise RuntimeError("openai provider unavailable") from exc

        if not settings.openai_api_key:
            raise RuntimeError("OPENAI_API_KEY is required for openai provider")

        llm = ChatOpenAI(model=model, api_key=settings.openai_api_key, temperature=0)
        response = llm.invoke([
            ("system", system_prompt),
            ("human", user_prompt),
        ])
        return str(response.content)

    if normalized == "ollama":
        try:
            from langchain_ollama import ChatOllama
        except Exception as exc:
            raise RuntimeError("ollama provider unavailable") from exc

        llm = ChatOllama(model=model, base_url=settings.ollama_base_url, temperature=0)
        response = llm.invoke([
            ("system", system_prompt),
            ("human", user_prompt),
        ])
        return str(response.content)

    raise ValueError(f"Unsupported provider: {provider}")


def generate_with_settings(system_prompt: str, user_prompt: str) -> str:
    provider = settings.llm_provider
    model = settings.openai_model if provider == "openai" else settings.ollama_model
    if provider == "stub":
        model = "demo"

    return generate_response(
        provider=provider,
        model=model,
        system_prompt=system_prompt,
        user_prompt=user_prompt,
    )
