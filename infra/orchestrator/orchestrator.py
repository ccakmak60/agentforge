import json
import os
import time

import boto3


SQS_ENDPOINT_URL = os.getenv("SQS_ENDPOINT_URL", "http://elasticmq:9324")
AWS_REGION = os.getenv("AWS_REGION", "us-east-1")
TEAM_ORDER = os.getenv("TEAM_ORDER", "researcher,summarizer,reviewer").split(",")
TASK_QUEUE = os.getenv("TASK_QUEUE", "task-queue")
AGENT_CHAT_QUEUE = os.getenv("AGENT_CHAT_QUEUE", "agent-chat")
RESULT_QUEUE = os.getenv("RESULT_QUEUE", "result-queue")

sqs = boto3.client(
    "sqs",
    endpoint_url=SQS_ENDPOINT_URL,
    region_name=AWS_REGION,
    aws_access_key_id="x",
    aws_secret_access_key="x",
)


def queue_url(name: str) -> str:
    try:
        return sqs.get_queue_url(QueueName=name)["QueueUrl"]
    except Exception:
        return sqs.create_queue(QueueName=name)["QueueUrl"]


def compute_routing_decision(payload: dict, default_team_order: list[str]) -> dict:
    if payload.get("failed_role"):
        failed_role = str(payload.get("failed_role"))
        retry_count = int(payload.get("retry_count", 0))
        max_retries = int(payload.get("max_retries", 1))

        if retry_count < max_retries:
            return {
                "action": "retry",
                "target_role": failed_role,
                "retry_count": retry_count + 1,
            }

        return {"action": "fail"}

    payload_order = payload.get("agent_order")
    order = payload_order if isinstance(payload_order, list) and payload_order else default_team_order

    normalized_order = [str(role) for role in order]
    current_role = str(payload.get("completed_role", ""))
    idx = normalized_order.index(current_role) if current_role in normalized_order else -1
    next_idx = idx + 1

    if 0 <= next_idx < len(normalized_order):
        return {
            "action": "route",
            "target_role": normalized_order[next_idx],
        }

    return {"action": "complete"}


def main() -> None:
    task_queue_url = queue_url(TASK_QUEUE)
    chat_queue_url = queue_url(AGENT_CHAT_QUEUE)
    result_queue_url = queue_url(RESULT_QUEUE)

    print("orchestrator started")
    while True:
        msgs = sqs.receive_message(
            QueueUrl=chat_queue_url,
            MaxNumberOfMessages=1,
            WaitTimeSeconds=10,
        ).get("Messages", [])

        for m in msgs:
            body = json.loads(m["Body"])
            decision = compute_routing_decision(body, TEAM_ORDER)

            if decision["action"] == "retry":
                body["target_role"] = decision["target_role"]
                body["retry_count"] = decision["retry_count"]
                body.pop("failed_role", None)
                body.pop("error", None)
                sqs.send_message(QueueUrl=task_queue_url, MessageBody=json.dumps(body))
            elif decision["action"] == "route":
                body["target_role"] = decision["target_role"]
                body.pop("completed_role", None)
                sqs.send_message(QueueUrl=task_queue_url, MessageBody=json.dumps(body))
            elif decision["action"] == "fail":
                body["status"] = "failed"
                sqs.send_message(QueueUrl=result_queue_url, MessageBody=json.dumps(body))
            else:
                body["status"] = "completed"
                sqs.send_message(QueueUrl=result_queue_url, MessageBody=json.dumps(body))

            sqs.delete_message(QueueUrl=chat_queue_url, ReceiptHandle=m["ReceiptHandle"])

        time.sleep(0.5)


if __name__ == "__main__":
    main()
