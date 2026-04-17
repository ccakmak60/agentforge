# Laravel Dashboard API Parity Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the current PHP scaffold in `dashboard/` with a Laravel 11 API that preserves the existing AgentForge endpoint contract.

**Architecture:** Build a Laravel API-only backend inside `dashboard/` with Eloquent models for `agents`, `teams`, and `tasks`, queue publishing through an SQS adapter, and webhook/result ingestion compatible with the existing orchestrator flow. Keep response shapes compatible with current clients so infra and agents remain unchanged.

**Tech Stack:** Laravel 11, PHP 8.2+, PHPUnit/Pest feature tests, Eloquent ORM, AWS SDK for PHP (SQS), DynamoDB-compatible storage bridge (phase 2 fallback: MySQL/SQLite local for config + task rows).

---

## File Structure Map

- Create `dashboard/composer.json` (Laravel dependencies and scripts).
- Create `dashboard/artisan` and `dashboard/bootstrap/app.php` (Laravel runtime entry).
- Create `dashboard/routes/api.php` (endpoint contract: `/api/agents`, `/api/teams`, `/api/tasks`, `/api/tasks/{id}/status`, `/api/tasks/{id}/conversation`, `/api/webhooks/n8n`).
- Create `dashboard/app/Models/Agent.php`, `Team.php`, `Task.php` (domain persistence).
- Create `dashboard/app/Http/Controllers/AgentController.php`, `TeamController.php`, `TaskController.php`, `WebhookController.php`.
- Create `dashboard/app/Services/SqsPublisher.php` and `dashboard/app/Services/ResultIngestor.php`.
- Create `dashboard/database/migrations/*_create_agents_table.php`, `*_create_teams_table.php`, `*_create_tasks_table.php`.
- Create `dashboard/tests/Feature/AgentApiTest.php`, `TeamApiTest.php`, `TaskApiTest.php`, `WebhookApiTest.php`.
- Modify `dashboard/Dockerfile` to run Laravel public entrypoint.
- Modify `infra/docker-compose.yml` dashboard service env vars and mount paths only if required by Laravel runtime.

---

### Task 1: Bootstrap Laravel API Skeleton

**Files:**
- Create: `dashboard/composer.json`
- Create: `dashboard/bootstrap/app.php`
- Create: `dashboard/public/index.php`
- Create: `dashboard/routes/api.php`
- Create: `dashboard/tests/Feature/HealthTest.php`

- [ ] **Step 1: Write the failing health test**

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_api_root_returns_service_status(): void
    {
        $response = $this->getJson('/');

        $response->assertStatus(200)
            ->assertJson([
                'service' => 'agentforge-dashboard',
                'status' => 'ok',
            ]);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `composer test --working-dir=dashboard -- --filter=HealthTest`
Expected: FAIL with bootstrap/framework missing errors.

- [ ] **Step 3: Add minimal Laravel bootstrap + route**

```php
// dashboard/routes/api.php
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'service' => 'agentforge-dashboard',
        'status' => 'ok',
        'routes' => [
            'POST /api/agents',
            'POST /api/teams',
            'POST /api/tasks',
            'GET /api/tasks/{id}/status',
            'GET /api/tasks/{id}/conversation',
            'POST /api/webhooks/n8n',
        ],
    ]);
});
```

- [ ] **Step 4: Re-run test to verify it passes**

Run: `composer test --working-dir=dashboard -- --filter=HealthTest`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add dashboard/composer.json dashboard/bootstrap dashboard/public dashboard/routes dashboard/tests/Feature/HealthTest.php
git commit -m "feat(dashboard): bootstrap laravel api skeleton"
```

---

### Task 2: Implement Agent/Team APIs with Persistence

**Files:**
- Create: `dashboard/app/Models/Agent.php`
- Create: `dashboard/app/Models/Team.php`
- Create: `dashboard/database/migrations/2026_04_17_000001_create_agents_table.php`
- Create: `dashboard/database/migrations/2026_04_17_000002_create_teams_table.php`
- Create: `dashboard/app/Http/Controllers/AgentController.php`
- Create: `dashboard/app/Http/Controllers/TeamController.php`
- Modify: `dashboard/routes/api.php`
- Test: `dashboard/tests/Feature/AgentApiTest.php`
- Test: `dashboard/tests/Feature/TeamApiTest.php`

- [ ] **Step 1: Write failing API tests for create agent/team**

```php
public function test_create_agent_returns_201_with_payload(): void
{
    $payload = [
        'name' => 'Research 1',
        'role' => 'researcher',
        'llm_model' => 'gpt-4o-mini',
        'system_prompt' => 'Research thoroughly',
        'temperature' => 0.2,
    ];

    $this->postJson('/api/agents', $payload)
        ->assertStatus(201)
        ->assertJsonFragment([
            'name' => 'Research 1',
            'role' => 'researcher',
        ]);
}

public function test_create_team_requires_non_empty_agent_order(): void
{
    $this->postJson('/api/teams', [
        'name' => 'Default Team',
        'agent_order' => [],
    ])->assertStatus(422);
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `composer test --working-dir=dashboard -- --filter="AgentApiTest|TeamApiTest"`
Expected: FAIL with `404`/missing table/controller errors.

- [ ] **Step 3: Implement minimal models, migrations, controllers, and routes**

```php
// dashboard/routes/api.php (append)
Route::post('/api/agents', [AgentController::class, 'store']);
Route::post('/api/teams', [TeamController::class, 'store']);

// AgentController::store() validates and creates
$validated = $request->validate([
    'name' => ['required', 'string'],
    'role' => ['required', 'string'],
    'llm_model' => ['required', 'string'],
    'system_prompt' => ['required', 'string'],
    'temperature' => ['nullable', 'numeric'],
]);

$agent = Agent::query()->create($validated + ['temperature' => $validated['temperature'] ?? 0.2]);
return response()->json($agent, 201);
```

- [ ] **Step 4: Re-run focused tests**

Run: `composer test --working-dir=dashboard -- --filter="AgentApiTest|TeamApiTest"`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add dashboard/app dashboard/database/migrations dashboard/routes/api.php dashboard/tests/Feature/AgentApiTest.php dashboard/tests/Feature/TeamApiTest.php
git commit -m "feat(dashboard): add agent and team api endpoints"
```

---

### Task 3: Implement Task Submission, Status, Conversation, and Webhook

**Files:**
- Create: `dashboard/app/Models/Task.php`
- Create: `dashboard/database/migrations/2026_04_17_000003_create_tasks_table.php`
- Create: `dashboard/app/Services/SqsPublisher.php`
- Create: `dashboard/app/Http/Controllers/TaskController.php`
- Create: `dashboard/app/Http/Controllers/WebhookController.php`
- Modify: `dashboard/routes/api.php`
- Test: `dashboard/tests/Feature/TaskApiTest.php`
- Test: `dashboard/tests/Feature/WebhookApiTest.php`

- [ ] **Step 1: Write failing tests for task lifecycle**

```php
public function test_submit_task_queues_work_and_returns_202(): void
{
    $team = Team::factory()->create(['agent_order' => ['researcher', 'summarizer', 'reviewer']]);

    $this->postJson('/api/tasks', [
        'team_id' => $team->id,
        'input' => 'Summarize Kubernetes autoscaling strategies',
        'max_retries' => 1,
    ])->assertStatus(202)
      ->assertJsonStructure(['task_id', 'status', 'team_id']);
}

public function test_webhook_updates_task_status_and_result(): void
{
    $task = Task::factory()->create(['status' => 'queued']);

    $this->postJson('/api/webhooks/n8n', [
        'task_id' => $task->id,
        'status' => 'completed',
        'conversation' => [['role' => 'reviewer', 'content' => 'done']],
    ])->assertStatus(200);
}
```

- [ ] **Step 2: Run tests to verify failure**

Run: `composer test --working-dir=dashboard -- --filter="TaskApiTest|WebhookApiTest"`
Expected: FAIL with missing model/routes/service errors.

- [ ] **Step 3: Implement controller + service logic**

```php
// TaskController::store()
$task = Task::query()->create([
    'team_id' => $team->id,
    'input' => $validated['input'],
    'status' => 'queued',
    'conversation' => [],
    'result' => null,
]);

$this->sqsPublisher->send('task-queue', [
    'task_id' => (string) $task->id,
    'team_id' => (int) $team->id,
    'input' => $task->input,
    'agent_order' => $team->agent_order,
    'max_retries' => (int) ($validated['max_retries'] ?? 1),
    'target_role' => $team->agent_order[0],
    'conversation' => [],
]);

return response()->json([
    'task_id' => (string) $task->id,
    'status' => 'queued',
    'team_id' => (int) $team->id,
], 202);
```

- [ ] **Step 4: Re-run lifecycle tests**

Run: `composer test --working-dir=dashboard -- --filter="TaskApiTest|WebhookApiTest"`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add dashboard/app/Http/Controllers dashboard/app/Models/Task.php dashboard/app/Services/SqsPublisher.php dashboard/database/migrations dashboard/routes/api.php dashboard/tests/Feature/TaskApiTest.php dashboard/tests/Feature/WebhookApiTest.php
git commit -m "feat(dashboard): implement task lifecycle and n8n webhook"
```

---

### Task 4: Runtime Wiring and Regression Coverage

**Files:**
- Modify: `dashboard/Dockerfile`
- Modify: `infra/docker-compose.yml`
- Create: `dashboard/tests/Feature/ContractParityTest.php`
- Modify: `dashboard/README.md`

- [ ] **Step 1: Write failing contract parity tests**

```php
public function test_status_endpoint_returns_expected_shape(): void
{
    $task = Task::factory()->create(['status' => 'queued']);

    $this->getJson("/api/tasks/{$task->id}/status")
        ->assertStatus(200)
        ->assertJsonStructure(['task_id', 'status', 'updated_at']);
}
```

- [ ] **Step 2: Run parity tests and observe failures**

Run: `composer test --working-dir=dashboard -- --filter=ContractParityTest`
Expected: FAIL until route wiring/runtime config is complete.

- [ ] **Step 3: Update runtime wiring and docs**

```dockerfile
# dashboard/Dockerfile (runtime command)
CMD ["php", "-S", "0.0.0.0:8080", "-t", "public"]
```

```yaml
# infra/docker-compose.yml (dashboard env excerpt)
environment:
  APP_ENV: local
  APP_DEBUG: "true"
  APP_URL: http://localhost:8080
  SQS_ENDPOINT_URL: http://elasticmq:9324
  AWS_REGION: us-east-1
```

- [ ] **Step 4: Run full dashboard test suite**

Run: `composer test --working-dir=dashboard`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add dashboard/Dockerfile dashboard/README.md dashboard/tests/Feature/ContractParityTest.php infra/docker-compose.yml
git commit -m "chore(dashboard): wire runtime and lock api contract tests"
```

---

## Self-Review

- **Spec coverage:** This plan covers the design’s dashboard API contract and task lifecycle integration with SQS + n8n webhook. Livewire UI pages and logs page UX are intentionally deferred to a follow-up plan.
- **Placeholder scan:** No `TODO`/`TBD` placeholders present; each task includes concrete files, commands, and expected outcomes.
- **Type consistency:** Endpoint and payload names match existing scaffold contract (`team_id`, `agent_order`, `task_id`, `conversation`, `status`, `result`).
