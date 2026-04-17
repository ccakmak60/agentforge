import sys
from pathlib import Path
import unittest
import types


sys.path.insert(0, str(Path(__file__).resolve().parents[1]))

if "boto3" not in sys.modules:
    fake_boto3 = types.ModuleType("boto3")

    class _FakeClient:
        pass

    def _fake_client(*args, **kwargs):
        return _FakeClient()

    fake_boto3.client = _fake_client
    sys.modules["boto3"] = fake_boto3

from orchestrator import compute_routing_decision  # type: ignore  # noqa: E402


class RoutingDecisionTests(unittest.TestCase):
    def test_routes_using_payload_team_order(self) -> None:
        payload = {
            "task_id": "task-1",
            "completed_role": "researcher",
            "agent_order": ["researcher", "reviewer"],
        }

        decision = compute_routing_decision(payload, ["researcher", "summarizer", "reviewer"])

        self.assertEqual(decision["action"], "route")
        self.assertEqual(decision["target_role"], "reviewer")

    def test_completes_when_payload_order_finished(self) -> None:
        payload = {
            "task_id": "task-2",
            "completed_role": "reviewer",
            "agent_order": ["researcher", "reviewer"],
        }

        decision = compute_routing_decision(payload, ["researcher", "summarizer", "reviewer"])

        self.assertEqual(decision["action"], "complete")

    def test_falls_back_to_default_order_when_missing_payload_order(self) -> None:
        payload = {
            "task_id": "task-3",
            "completed_role": "researcher",
        }

        decision = compute_routing_decision(payload, ["researcher", "summarizer", "reviewer"])

        self.assertEqual(decision["action"], "route")
        self.assertEqual(decision["target_role"], "summarizer")

    def test_retries_failed_role_once(self) -> None:
        payload = {
            "task_id": "task-4",
            "failed_role": "summarizer",
            "error": "temporary timeout",
            "retry_count": 0,
        }

        decision = compute_routing_decision(payload, ["researcher", "summarizer", "reviewer"])

        self.assertEqual(decision["action"], "retry")
        self.assertEqual(decision["target_role"], "summarizer")
        self.assertEqual(decision["retry_count"], 1)

    def test_marks_failed_after_retry_limit(self) -> None:
        payload = {
            "task_id": "task-5",
            "failed_role": "summarizer",
            "error": "temporary timeout",
            "retry_count": 1,
        }

        decision = compute_routing_decision(payload, ["researcher", "summarizer", "reviewer"])

        self.assertEqual(decision["action"], "fail")


if __name__ == "__main__":
    unittest.main()
