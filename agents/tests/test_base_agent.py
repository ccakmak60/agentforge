import sys
from pathlib import Path
import unittest
from unittest.mock import Mock


sys.path.insert(0, str(Path(__file__).resolve().parents[1]))

from base_agent import BaseAgent  # type: ignore  # noqa: E402


class DummyAgent(BaseAgent):
    role = "dummy"

    def __init__(self) -> None:
        self.sqs = Mock()
        self.task_queue_url = "task-url"
        self.agent_chat_queue_url = "chat-url"
        self.result_queue_url = "result-url"

    def process(self, payload: dict) -> str:
        return "processed " + str(payload.get("task_id"))


class BaseAgentMessageHandlingTests(unittest.TestCase):
    def test_releases_messages_targeted_to_another_role(self) -> None:
        agent = DummyAgent()
        message = {
            "Body": '{"task_id":"task-1","target_role":"researcher"}',
            "ReceiptHandle": "receipt-1",
        }

        agent._handle_message(message)

        agent.sqs.change_message_visibility.assert_called_once_with(
            QueueUrl="task-url",
            ReceiptHandle="receipt-1",
            VisibilityTimeout=0,
        )
        agent.sqs.send_message.assert_not_called()
        agent.sqs.delete_message.assert_not_called()

    def test_processes_matching_role_and_deletes_message(self) -> None:
        agent = DummyAgent()
        message = {
            "Body": '{"task_id":"task-2","target_role":"dummy","conversation":[]}',
            "ReceiptHandle": "receipt-2",
        }

        agent._handle_message(message)

        agent.sqs.send_message.assert_called_once()
        agent.sqs.delete_message.assert_called_once_with(
            QueueUrl="task-url",
            ReceiptHandle="receipt-2",
        )
        agent.sqs.change_message_visibility.assert_not_called()


if __name__ == "__main__":
    unittest.main()
