output "task_queue_url" {
  description = "URL of the task queue"
  value = aws_sqs_queue.task_queue.url
}

output "agent_chat_queue_url" {
  description = "URL of the agent chat queue"
  value = aws_sqs_queue.agent_chat.url
}

output "result_queue_url" {
  description = "URL of the result queue"
  value = aws_sqs_queue.result_queue.url
}

output "artifacts_bucket" {
  description = "Artifacts S3 bucket name"
  value = aws_s3_bucket.artifacts.bucket
}

output "dynamodb_table_name" {
  description = "Task logs DynamoDB table name"
  value = aws_dynamodb_table.task_logs.name
}

output "name_prefix" {
  description = "Computed resource name prefix"
  value       = local.name_prefix
}

output "queue_names" {
  description = "Queue names provisioned by this stack"
  value = {
    task   = aws_sqs_queue.task_queue.name
    chat   = aws_sqs_queue.agent_chat.name
    result = aws_sqs_queue.result_queue.name
  }
}
