FROM python:3.12-slim

RUN apt-get update \
    && apt-get install -y --no-install-recommends docker.io \
    && rm -rf /var/lib/apt/lists/*

RUN pip install --no-cache-dir harbor

WORKDIR /workspace
CMD ["python", "benchmarks/harbor/runner/server.py"]
