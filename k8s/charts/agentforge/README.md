# AgentForge Helm Chart

This chart deploys AgentForge Python agent Deployments, ConfigMap/Secret wiring,
and optional KEDA-based autoscaling.

## KEDA configuration

When `keda.enabled=true`, you must provide a valid SQS queue URL in `keda.queueUrl`.
The chart now hard-fails rendering if that value is empty.

### Common values

```yaml
keda:
  enabled: true
  pollingInterval: 30
  cooldownPeriod: 300
  minReplicaCount: 1
  maxReplicaCount: 5
  queueLength: "5"
  queueUrl: https://sqs.us-east-1.amazonaws.com/123456789012/task-queue
  identityOwner: operator
  authenticationRef:
    enabled: false
    name: ""
```

### Production auth (optional)

If your cluster uses a KEDA `TriggerAuthentication` for AWS credentials/role
access, enable and reference it:

```yaml
keda:
  authenticationRef:
    enabled: true
    name: agentforge-keda-auth
```

This will add `authenticationRef` to each generated `ScaledObject` trigger.

## Production example values

A production-oriented example is provided in `values-prod.yaml` with:

- `keda.authenticationRef.enabled: true`
- non-default scaling thresholds and replica limits
- resource requests/limits per agent
- external secret usage (`secrets.create: false`)

Example install command:

```bash
helm upgrade --install agentforge ./k8s/charts/agentforge -f ./k8s/charts/agentforge/values-prod.yaml
```

## Development example values

A local/dev profile is provided in `values-dev.yaml` with:

- `llm.provider: stub`
- `keda.enabled: false` for simpler local behavior
- single-replica agents and minimal defaults

Example install command:

```bash
helm upgrade --install agentforge ./k8s/charts/agentforge -f ./k8s/charts/agentforge/values-dev.yaml
```
