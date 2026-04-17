import json
import uuid

import boto3


def main() -> None:
    sqs = boto3.client(
        "sqs",
        endpoint_url="http://localhost:9324",
        region_name="us-east-1",
        aws_access_key_id="x",
        aws_secret_access_key="x",
    )
    queue = sqs.get_queue_url(QueueName="task-queue")["QueueUrl"]

    payload = {
        "task_id": str(uuid.uuid4()),
        "input": "Create a concise overview of Kubernetes autoscaling for engineers.",
        "agent_order": ["researcher", "summarizer", "reviewer"],
        "max_retries": 1,
        "target_role": "researcher",
        "conversation": [],
    }

    sqs.send_message(QueueUrl=queue, MessageBody=json.dumps(payload))
    print(f"Submitted task {payload['task_id']}")


if __name__ == "__main__":
    main()
