"""HTTP-backed load_skill tool for AgentForge agents.

Exposes a LangChain StructuredTool that fetches a skill body from the
dashboard API on demand. Skills are not pasted into the system prompt --
the agent only sees a short index (slug + name + description) and calls
load_skill(slug) when it decides a skill is relevant.
"""

from __future__ import annotations

import json
import logging
import os
from typing import Dict, List, Optional
from urllib import error, parse, request

from langchain_core.tools import StructuredTool
from pydantic import BaseModel, Field

logger = logging.getLogger(__name__)

DEFAULT_TIMEOUT_SECONDS = 8
DEFAULT_MAX_BODY_CHARS = 8000


class LoadSkillInput(BaseModel):
    slug: str = Field(
        ..., description="Slug of the skill to load, e.g. 'react-best-practices'."
    )


def _truncate(text: str, limit: int) -> str:
    if len(text) <= limit:
        return text
    return text[:limit] + "\n\n[...truncated]"


def _http_get_json(url: str, timeout: int) -> Optional[Dict]:
    req = request.Request(url, headers={"Accept": "application/json"})
    try:
        with request.urlopen(req, timeout=timeout) as response:
            payload = response.read().decode("utf-8")
    except error.HTTPError as exc:
        logger.warning("load_skill HTTP %s for %s", exc.code, url)
        return None
    except (error.URLError, TimeoutError) as exc:
        logger.warning("load_skill transport error for %s: %s", url, exc)
        return None

    try:
        decoded = json.loads(payload)
    except json.JSONDecodeError:
        logger.warning("load_skill non-JSON response from %s", url)
        return None

    return decoded if isinstance(decoded, dict) else None


def make_load_skill_tool(
    base_url: Optional[str] = None,
    available_slugs: Optional[List[str]] = None,
    *,
    timeout: int = DEFAULT_TIMEOUT_SECONDS,
    max_chars: int = DEFAULT_MAX_BODY_CHARS,
) -> StructuredTool:
    """Build a LangChain tool that fetches /api/skills/{slug} from the dashboard.

    The tool description includes the available skill slugs so the model
    knows which arguments are valid without us having to put skill bodies
    in the system prompt.
    """

    resolved_base = (base_url or os.getenv("DASHBOARD_BASE_URL") or "http://dashboard").rstrip("/")
    cache: Dict[str, str] = {}
    allowed = set(available_slugs or [])

    def _load(slug: str) -> str:
        slug = (slug or "").strip()
        if not slug:
            return "ERROR: slug is required"
        if allowed and slug not in allowed:
            return (
                f"ERROR: skill '{slug}' is not attached to this agent. "
                f"Available skills: {sorted(allowed)}"
            )
        if slug in cache:
            return cache[slug]

        url = f"{resolved_base}/api/skills/{parse.quote(slug, safe='')}"
        data = _http_get_json(url, timeout=timeout)
        if data is None:
            return f"ERROR: could not load skill '{slug}'"

        body = str(data.get("body") or "")
        name = str(data.get("name") or slug)
        if not body:
            return f"ERROR: skill '{slug}' has no body"

        rendered = f"# {name}\n\n{body}"
        truncated = _truncate(rendered, max_chars)
        cache[slug] = truncated
        return truncated

    description_lines = [
        "Load the full instructions for a named skill from the company skill library.",
        "Use this when the current task matches a skill's name or description.",
        "Returns the full skill markdown body. Call this at most once per skill.",
    ]
    if allowed:
        description_lines.append(
            "Available skill slugs: " + ", ".join(sorted(allowed))
        )

    return StructuredTool.from_function(
        func=_load,
        name="load_skill",
        description="\n".join(description_lines),
        args_schema=LoadSkillInput,
    )


def render_skill_index(skills: List[Dict]) -> str:
    """Render the skill index that the agent sees in its system prompt."""
    if not skills:
        return ""
    lines = [
        "Available skills (call load_skill(slug) to read full instructions):",
    ]
    for skill in skills:
        slug = str(skill.get("slug") or "")
        name = str(skill.get("name") or slug)
        description = str(skill.get("description") or "").strip().replace("\n", " ")
        if not slug:
            continue
        lines.append(f"- {slug} ({name}): {description}")
    return "\n".join(lines)
