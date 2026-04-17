terraform {
  required_version = ">= 1.5.0"
  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 5.0"
    }
  }
}

provider "aws" {
  region = var.aws_region
}

locals {
  name_prefix = "${var.project_name}-${var.environment}"

  artifacts_bucket_name = var.artifacts_bucket_name_override != "" ? var.artifacts_bucket_name_override : "${local.name_prefix}-artifacts"

  common_tags = merge(
    {
      Project     = var.project_name
      Environment = var.environment
      ManagedBy   = "terraform"
    },
    var.common_tags
  )
}

resource "aws_sqs_queue" "task_queue" {
  name = var.queue_names.task
  tags = local.common_tags
}

resource "aws_sqs_queue" "agent_chat" {
  name = var.queue_names.chat
  tags = local.common_tags
}

resource "aws_sqs_queue" "result_queue" {
  name = var.queue_names.result
  tags = local.common_tags
}

resource "aws_s3_bucket" "artifacts" {
  bucket = local.artifacts_bucket_name
  tags   = local.common_tags
}

resource "aws_dynamodb_table" "task_logs" {
  name         = "${local.name_prefix}-task-logs"
  billing_mode = "PAY_PER_REQUEST"
  hash_key     = "task_id"
  tags         = local.common_tags

  attribute {
    name = "task_id"
    type = "S"
  }
}
