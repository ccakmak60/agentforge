import sys
from pathlib import Path
import unittest


sys.path.insert(0, str(Path(__file__).resolve().parents[1]))

from llm_client import generate_response  # type: ignore  # noqa: E402


class LlmClientTests(unittest.TestCase):
    def test_stub_provider_returns_deterministic_text(self) -> None:
        output = generate_response(
            provider="stub",
            model="demo",
            system_prompt="You are a helper",
            user_prompt="Explain autoscaling",
        )

        self.assertIn("[stub:demo]", output)
        self.assertIn("Explain autoscaling", output)

    def test_unsupported_provider_raises(self) -> None:
        with self.assertRaises(ValueError):
            generate_response(
                provider="unknown",
                model="x",
                system_prompt="s",
                user_prompt="u",
            )


if __name__ == "__main__":
    unittest.main()
