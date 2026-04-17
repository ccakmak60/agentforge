# AgentForge Terraform

This folder provisions core AWS resources for AgentForge:

- SQS queues (`task`, `chat`, `result`)
- S3 bucket for artifacts
- DynamoDB table for task logs

## Usage

```bash
terraform init
terraform plan -var-file=terraform.tfvars
terraform apply -var-file=terraform.tfvars
```

## Configuration

1. Copy `terraform.tfvars.example` to `terraform.tfvars`.
2. Set `project_name` and `environment` for your target stack.
3. Optionally set `artifacts_bucket_name_override`.
4. Add any `common_tags` required by your org.

## Outputs

Key outputs include queue URLs, bucket name, table name, and computed name prefix.
