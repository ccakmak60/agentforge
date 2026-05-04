from typing import Dict, List, Optional

from config import settings


def _build_chat_model(provider: str, model: str, temperature: float = 0.0):
    normalized = provider.lower().strip()
    if normalized in ("openai",):
        from langchain_openai import ChatOpenAI

        if not settings.openai_api_key:
            raise RuntimeError("OPENAI_API_KEY is required for openai provider")
        return ChatOpenAI(model=model, api_key=settings.openai_api_key, temperature=temperature)

    if normalized == "nim":
        from langchain_openai import ChatOpenAI

        if not settings.nim_api_key:
            raise RuntimeError("NIM_API_KEY is required for nim provider")
        return ChatOpenAI(
            model=model or settings.nim_model,
            api_key=settings.nim_api_key,
            base_url=settings.nim_base_url,
            temperature=temperature,
        )

    if normalized == "ollama":
        from langchain_ollama import ChatOllama

        return ChatOllama(model=model, base_url=settings.ollama_base_url, temperature=temperature)

    raise ValueError(f"Unsupported provider: {provider}")


def run_agent_with_skills(
    *,
    provider: str,
    model: str,
    system_prompt: str,
    user_prompt: str,
    skills: Optional[List[Dict]] = None,
    temperature: float = 0.0,
    max_iterations: int = 4,
) -> str:
    """Run a tool-calling agent loop that exposes load_skill(slug).

    Skills are attached as tool-accessible context, not injected into the
    system prompt. Full skill markdown is fetched only when the model calls
    the tool.
    """

    normalized = provider.lower().strip()
    if normalized == "stub":
        return f"[stub:{model}] {user_prompt}"

    skills = skills or []
    if not skills:
        return generate_response(
            provider=provider,
            model=model,
            system_prompt=system_prompt,
            user_prompt=user_prompt,
        )

    from langchain.agents import AgentExecutor, create_tool_calling_agent
    from langchain_core.prompts import ChatPromptTemplate, MessagesPlaceholder

    from skill_tool import make_load_skill_tool, render_skill_index

    available_slugs = [str(s.get("slug") or "") for s in skills if s.get("slug")]
    skill_index = render_skill_index(skills)
    contextual_input = user_prompt
    if skill_index:
        contextual_input = (
            f"{user_prompt}\n\n"
            f"Attached agent skills are available as tools, not system instructions.\n"
            f"{skill_index}"
        )

    chat = _build_chat_model(provider, model, temperature=temperature)
    tools = [make_load_skill_tool(available_slugs=available_slugs)]

    prompt = ChatPromptTemplate.from_messages([
        ("system", system_prompt),
        ("human", "{input}"),
        MessagesPlaceholder("agent_scratchpad"),
    ])

    agent = create_tool_calling_agent(chat, tools, prompt)
    executor = AgentExecutor(
        agent=agent,
        tools=tools,
        max_iterations=max_iterations,
        verbose=False,
        return_intermediate_steps=False,
        handle_parsing_errors=True,
    )
    result = executor.invoke({"input": contextual_input})
    output = result.get("output") if isinstance(result, dict) else result
    return str(output)


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

    if normalized == "nim":
        try:
            from langchain_openai import ChatOpenAI
        except Exception as exc:
            raise RuntimeError("nim provider unavailable (needs langchain-openai)") from exc

        if not settings.nim_api_key:
            raise RuntimeError("NIM_API_KEY is required for nim provider")

        effective_model = model or settings.nim_model
        llm = ChatOpenAI(
            model=effective_model,
            api_key=settings.nim_api_key,
            base_url=settings.nim_base_url,
            temperature=0,
        )
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
    if provider == "nim":
        model = settings.nim_model
    if provider == "stub":
        model = "demo"

    return generate_response(
        provider=provider,
        model=model,
        system_prompt=system_prompt,
        user_prompt=user_prompt,
    )
