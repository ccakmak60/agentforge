import json
import logging
import time
from abc import ABC, abstractmethod
from typing import Any, Dict

import boto3
from botocore.exceptions import ClientError

from config import settings


logging.basicConfig(level=logging.INFO, format="%(asctime)s %(levelname)s %(message)s")
logger = logging.getLogger(__name__)


class BaseAgent(ABC):
    role = "base"

    def __init__(self) -> None:
        self.sqs = boto3.client(
            "sqs",
            region_name=settings.aws_region,
            endpoint_url=settings.sqs_endpoint_url,
            aws_access_key_id="x",
            aws_secret_access_key="x",
        )
        self.task_queue_url = self._resolve_queue(settings.task_queue)
        self.agent_chat_queue_url = self._resolve_queue(settings.agent_chat_queue)
        self.result_queue_url = self._resolve_queue(settings.result_queue)

    def _resolve_queue(self, queue_name: str) -> str:
        try:
            return self.sqs.get_queue_url(QueueName=queue_name)["QueueUrl"]
        except ClientError:
            logger.info("Queue %s missing; creating", queue_name)
            return self.sqs.create_queue(QueueName=queue_name)["QueueUrl"]

    def run(self) -> None:
        logger.info("%s agent started", self.role)
        while True:
            response = self.sqs.receive_message(
                QueueUrl=self.task_queue_url,
                MaxNumberOfMessages=1,
                WaitTimeSeconds=settings.poll_wait_seconds,
            )
            for message in response.get("Messages", []):
                self._handle_message(message)
            time.sleep(0.5)

    def _handle_message(self, message: Dict[str, Any]) -> None:
        payload = json.loads(message["Body"])
        target_role = payload.get("target_role")
        if target_role not in (None, self.role):
            return

        logger.info("%s processing task %s", self.role, payload.get("task_id"))
        try:
            output = self.process(payload)
            payload.setdefault("conversation", []).append(
                {
                    "role": self.role,
                    "content": output,
                    "timestamp": int(time.time()),
                }
            )
            payload["completed_role"] = self.role
        except Exception as exc:
            payload["failed_role"] = self.role
            payload["error"] = str(exc)

        self.sqs.send_message(QueueUrl=self.agent_chat_queue_url, MessageBody=json.dumps(payload))
        self.sqs.delete_message(QueueUrl=self.task_queue_url, ReceiptHandle=message["ReceiptHandle"])

    @abstractmethod
    def process(self, payload: Dict[str, Any]) -> str:
        raise NotImplementedError
