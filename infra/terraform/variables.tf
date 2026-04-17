variable "aws_region" {
  type    = string
  default = "us-east-1"
}

variable "project_name" {
  type    = string
  default = "agentforge"
}

variable "environment" {
  type    = string
  default = "dev"
}

variable "queue_names" {
  type = object({
    task   = string
    chat   = string
    result = string
  })

  default = {
    task   = "task-queue"
    chat   = "agent-chat"
    result = "result-queue"
  }
}

variable "artifacts_bucket_name_override" {
  type    = string
  default = ""
}

variable "common_tags" {
  type    = map(string)
  default = {}
}
