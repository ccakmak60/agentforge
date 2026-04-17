# Raw Kubernetes Manifests

These manifests are a flat alternative to the Helm chart in `charts/agentforge/`
and are intended for quick `kubectl apply` on a local cluster (minikube / kind).

## Layout

- `configmaps/agent-config.yaml` – queue names, AWS region, and LLM settings
  consumed by the Python agents (`agents/config.py`).
- `secrets/agent-secrets.yaml` – optional `OPENAI_API_KEY`. Replace the empty
  value before applying if you are using the `openai` LLM provider.
- `deployments/agents.yaml` – researcher, summarizer, and reviewer Deployments.
  All wire the ConfigMap keys as env vars and reference `OPENAI_API_KEY` from
  the Secret as an optional key.
- `hpa/agents-hpa.yaml` – KEDA `ScaledObject` resources that scale each
  Deployment based on the task SQS queue depth, including polling/cooldown
  controls and `identityOwner` metadata.

## Apply order

```bash
kubectl apply -f k8s/configmaps/agent-config.yaml
kubectl apply -f k8s/secrets/agent-secrets.yaml
kubectl apply -f k8s/deployments/agents.yaml
kubectl apply -f k8s/hpa/agents-hpa.yaml
```

## Notes

- The raw manifests and the Helm chart share the same env contract, so you can
  switch between them without changing the container image.
- For production, prefer the Helm chart which parameterizes replicas, resources,
  KEDA triggers (including `authenticationRef`), and the OpenAI secret.
- Update the KEDA `queueURL` in `hpa/agents-hpa.yaml` to point at your real SQS
  queue ARN/URL, or disable KEDA if you are running locally against ElasticMQ.
- For raw manifests, tune `pollingInterval` / `cooldownPeriod` in
  `hpa/agents-hpa.yaml` per environment scale behavior.
