import json

import boto3


def main() -> None:
    sqs = boto3.client(
        "sqs",
        endpoint_url="http://localhost:9324",
        region_name="us-east-1",
        aws_access_key_id="x",
        aws_secret_access_key="x",
    )
    queue = sqs.get_queue_url(QueueName="result-queue")["QueueUrl"]

    messages = sqs.receive_message(
        QueueUrl=queue,
        MaxNumberOfMessages=1,
        WaitTimeSeconds=2,
    ).get("Messages", [])

    if not messages:
        print("No messages in result-queue yet")
        return

    message = messages[0]
    body = json.loads(message["Body"])
    print(json.dumps(body, indent=2))


if __name__ == "__main__":
    main()
