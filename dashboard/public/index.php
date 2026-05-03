<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

function render_dashboard(): void
{
    header('Content-Type: text/html; charset=utf-8');
    echo <<<'HTML'
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AgentForge</title>
    <style>
        :root {
            --paper: #f7f8f8;
            --surface: #ffffff;
            --ink: #08090a;
            --muted: #62666d;
            --quiet: #8a8f98;
            --line: #d0d6e0;
            --line-soft: #e2e4e7;
            --pink: #f79ce0;
            --cyan: #55cdff;
            --amber: #ffc47c;
            --ok: #198754;
            --bad: #b42318;
            --radius: 8px;
        }

        * {
            box-sizing: border-box;
        }

        html {
            color: var(--ink);
            background:
                linear-gradient(90deg, rgba(208, 214, 224, 0.44) 1px, transparent 1px),
                linear-gradient(rgba(208, 214, 224, 0.38) 1px, transparent 1px),
                var(--paper);
            background-size: 48px 48px;
            font-family: "Aptos", "Segoe UI", sans-serif;
            letter-spacing: 0;
        }

        body {
            margin: 0;
            min-height: 100vh;
        }

        button,
        input,
        textarea,
        select {
            font: inherit;
        }

        button {
            cursor: pointer;
        }

        .app {
            display: grid;
            grid-template-columns: 248px minmax(0, 1fr);
            min-height: 100vh;
        }

        .rail {
            position: sticky;
            top: 0;
            height: 100vh;
            border-right: 1px solid var(--line);
            background: rgba(247, 248, 248, 0.92);
            padding: 18px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 22px;
        }

        .mark {
            display: grid;
            place-items: center;
            width: 34px;
            height: 34px;
            border: 1px solid var(--ink);
            border-radius: var(--radius);
            background: var(--surface);
            font-family: "Cascadia Mono", "Consolas", monospace;
            font-size: 12px;
            font-weight: 700;
        }

        .brand strong,
        .panel h2,
        .hero h1 {
            margin: 0;
            letter-spacing: 0;
        }

        .brand span,
        .eyebrow,
        .meta,
        .field span,
        .chip,
        .endpoint {
            font-family: "Cascadia Mono", "Consolas", monospace;
            font-size: 12px;
        }

        .brand span,
        .meta,
        .field span {
            color: var(--muted);
        }

        .nav {
            display: grid;
            gap: 6px;
        }

        .nav a {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 34px;
            padding: 8px 10px;
            color: var(--muted);
            text-decoration: none;
            border: 1px solid transparent;
            border-radius: var(--radius);
            font-size: 14px;
        }

        .nav a:hover,
        .nav a:focus-visible {
            color: var(--ink);
            border-color: var(--line);
            background: var(--surface);
            outline: none;
        }

        .rail-note {
            position: absolute;
            left: 18px;
            right: 18px;
            bottom: 18px;
            padding: 12px;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            background: var(--surface);
        }

        .main {
            padding: 22px;
        }

        .hero {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: end;
            gap: 20px;
            padding: 20px 0 24px;
            border-bottom: 1px solid var(--line);
        }

        .hero h1 {
            max-width: 760px;
            font-size: clamp(34px, 5vw, 72px);
            line-height: 0.94;
            font-weight: 760;
        }

        .hero p {
            max-width: 590px;
            margin: 14px 0 0;
            color: var(--muted);
            line-height: 1.55;
        }

        .status-card {
            width: min(100%, 330px);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            background: var(--surface);
            overflow: hidden;
        }

        .status-row {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 10px;
            padding: 10px 12px;
            border-top: 1px solid var(--line-soft);
            font-size: 13px;
        }

        .status-row:first-child {
            border-top: 0;
        }

        .chip {
            display: inline-flex;
            align-items: center;
            min-height: 22px;
            padding: 3px 7px;
            border: 1px solid var(--line);
            border-radius: 999px;
            background: var(--paper);
            color: var(--muted);
            white-space: nowrap;
        }

        .chip.hot {
            color: var(--ink);
            border-color: var(--ink);
            background: linear-gradient(90deg, var(--cyan), var(--pink));
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: 12px;
            margin-top: 12px;
        }

        .panel {
            min-width: 0;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            background: rgba(255, 255, 255, 0.94);
            overflow: hidden;
        }

        .panel.large {
            grid-column: span 8;
        }

        .panel.small {
            grid-column: span 4;
        }

        .panel.full {
            grid-column: 1 / -1;
        }

        .panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            min-height: 48px;
            padding: 12px 14px;
            border-bottom: 1px solid var(--line);
        }

        .panel h2 {
            font-size: 15px;
        }

        .panel-body {
            padding: 14px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .field {
            display: grid;
            gap: 6px;
        }

        .field.wide {
            grid-column: 1 / -1;
        }

        input,
        textarea,
        select {
            width: 100%;
            min-height: 38px;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            background: var(--paper);
            color: var(--ink);
            padding: 9px 10px;
            outline: none;
        }

        textarea {
            min-height: 92px;
            resize: vertical;
            line-height: 1.45;
        }

        input:focus,
        textarea:focus,
        select:focus {
            border-color: var(--ink);
            background: var(--surface);
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 38px;
            padding: 8px 12px;
            border: 1px solid var(--ink);
            border-radius: var(--radius);
            background: var(--ink);
            color: var(--surface);
        }

        .btn.secondary {
            border-color: var(--line);
            background: var(--surface);
            color: var(--ink);
        }

        .btn:disabled {
            cursor: not-allowed;
            opacity: 0.56;
        }

        .list {
            display: grid;
            gap: 8px;
        }

        .item {
            display: grid;
            gap: 8px;
            padding: 10px;
            border: 1px solid var(--line-soft);
            border-radius: var(--radius);
            background: var(--paper);
        }

        .item-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            min-width: 0;
        }

        .item-title strong,
        .truncate {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .pipeline {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .pipeline .chip:nth-child(3n + 1) {
            border-color: var(--cyan);
        }

        .pipeline .chip:nth-child(3n + 2) {
            border-color: var(--pink);
        }

        .pipeline .chip:nth-child(3n + 3) {
            border-color: var(--amber);
        }

        .empty {
            min-height: 126px;
            display: grid;
            place-items: center;
            color: var(--quiet);
            text-align: center;
            border: 1px dashed var(--line);
            border-radius: var(--radius);
            background: var(--paper);
        }

        .toast {
            position: fixed;
            right: 18px;
            bottom: 18px;
            z-index: 10;
            display: none;
            max-width: min(420px, calc(100vw - 36px));
            padding: 12px 14px;
            border: 1px solid var(--ink);
            border-radius: var(--radius);
            background: var(--surface);
            color: var(--ink);
        }

        .toast.show {
            display: block;
        }

        .toast.error {
            border-color: var(--bad);
            color: var(--bad);
        }

        .endpoint {
            overflow-x: auto;
            padding: 12px;
            border: 1px solid var(--line-soft);
            border-radius: var(--radius);
            background: var(--paper);
            color: var(--muted);
            white-space: nowrap;
        }

        .workspace-band {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
        }

        @media (max-width: 980px) {
            .app {
                grid-template-columns: 1fr;
            }

            .rail {
                position: relative;
                height: auto;
                border-right: 0;
                border-bottom: 1px solid var(--line);
            }

            .rail-note {
                position: static;
                margin-top: 16px;
            }

            .hero,
            .form-grid,
            .workspace-band {
                grid-template-columns: 1fr;
            }

            .panel.large,
            .panel.small {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 620px) {
            .main {
                padding: 14px;
            }

            .hero h1 {
                font-size: 38px;
            }

            .panel-head,
            .item-title {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="app">
        <aside class="rail">
            <div class="brand">
                <div class="mark">AF</div>
                <div>
                    <strong>AgentForge</strong>
                    <div class="meta">multi-agent ops</div>
                </div>
            </div>
            <nav class="nav" aria-label="Dashboard">
                <a href="#compose">Compose <span>01</span></a>
                <a href="#teams">Teams <span>02</span></a>
                <a href="#tasks">Tasks <span>03</span></a>
                <a href="/api">API <span>JSON</span></a>
            </nav>
            <div class="rail-note">
                <div class="meta">Local stack</div>
                <p class="meta">Create agents, chain roles, submit queue work, inspect status.</p>
            </div>
        </aside>

        <main class="main">
            <section class="hero">
                <div>
                    <div class="eyebrow">DASHBOARD / ORCHESTRATION</div>
                    <h1>Build, route, and watch agent teams.</h1>
                    <p>Lean control room for AgentForge. No decorative noise. Forms map directly to API endpoints, so every click is real.</p>
                </div>
                <div class="status-card" aria-label="System summary">
                    <div class="status-row"><span>agents</span><strong id="metric-agents">0</strong></div>
                    <div class="status-row"><span>teams</span><strong id="metric-teams">0</strong></div>
                    <div class="status-row"><span>tasks</span><strong id="metric-tasks">0</strong></div>
                    <div class="status-row"><span>api</span><span class="chip hot">online</span></div>
                </div>
            </section>

            <section class="grid" id="compose">
                <article class="panel large">
                    <div class="panel-head">
                        <h2>New Agent</h2>
                        <span class="chip">POST /api/agents</span>
                    </div>
                    <div class="panel-body">
                        <form id="agent-form">
                            <div class="form-grid">
                                <label class="field">
                                    <span>Name</span>
                                    <input name="name" value="Research 1" required>
                                </label>
                                <label class="field">
                                    <span>Role</span>
                                    <select name="role" required>
                                        <option value="researcher">researcher</option>
                                        <option value="summarizer">summarizer</option>
                                        <option value="reviewer">reviewer</option>
                                        <option value="planner">planner</option>
                                    </select>
                                </label>
                                <label class="field">
                                    <span>Model</span>
                                    <input name="llm_model" value="stub-v1" required>
                                </label>
                                <label class="field">
                                    <span>Temperature</span>
                                    <input name="temperature" type="number" value="0.2" min="0" max="2" step="0.1">
                                </label>
                                <label class="field wide">
                                    <span>System Prompt</span>
                                    <textarea name="system_prompt" required>Research thoroughly. Return crisp notes and sources when available.</textarea>
                                </label>
                            </div>
                            <div class="actions">
                                <button class="btn" type="submit">Create agent</button>
                                <button class="btn secondary" type="button" id="seed-agents">Seed trio</button>
                            </div>
                        </form>
                    </div>
                </article>

                <article class="panel small">
                    <div class="panel-head">
                        <h2>Agents</h2>
                        <span class="chip" id="agents-count">0</span>
                    </div>
                    <div class="panel-body">
                        <div class="list" id="agents-list"></div>
                    </div>
                </article>

                <article class="panel small" id="teams">
                    <div class="panel-head">
                        <h2>New Team</h2>
                        <span class="chip">POST /api/teams</span>
                    </div>
                    <div class="panel-body">
                        <form id="team-form">
                            <div class="form-grid">
                                <label class="field wide">
                                    <span>Name</span>
                                    <input name="name" value="Default Team" required>
                                </label>
                                <label class="field wide">
                                    <span>Agent Order</span>
                                    <input name="agent_order" value="researcher, summarizer, reviewer" required>
                                </label>
                            </div>
                            <div class="actions">
                                <button class="btn" type="submit">Create team</button>
                            </div>
                        </form>
                    </div>
                </article>

                <article class="panel large">
                    <div class="panel-head">
                        <h2>Teams</h2>
                        <span class="chip" id="teams-count">0</span>
                    </div>
                    <div class="panel-body">
                        <div class="list" id="teams-list"></div>
                    </div>
                </article>

                <article class="panel large" id="tasks">
                    <div class="panel-head">
                        <h2>Submit Task</h2>
                        <span class="chip">POST /api/tasks</span>
                    </div>
                    <div class="panel-body">
                        <form id="task-form">
                            <div class="form-grid">
                                <label class="field">
                                    <span>Team</span>
                                    <select name="team_id" required></select>
                                </label>
                                <label class="field">
                                    <span>Max Retries</span>
                                    <input name="max_retries" type="number" value="1" min="0" max="9">
                                </label>
                                <label class="field wide">
                                    <span>Input</span>
                                    <textarea name="input" required>Summarize Kubernetes autoscaling strategies.</textarea>
                                </label>
                            </div>
                            <div class="actions">
                                <button class="btn" type="submit">Queue task</button>
                                <button class="btn secondary" type="button" id="refresh">Refresh</button>
                            </div>
                        </form>
                    </div>
                </article>

                <article class="panel small">
                    <div class="panel-head">
                        <h2>API Surface</h2>
                        <span class="chip">live</span>
                    </div>
                    <div class="panel-body">
                        <div class="list">
                            <div class="endpoint">GET /api/agents</div>
                            <div class="endpoint">GET /api/teams</div>
                            <div class="endpoint">GET /api/tasks</div>
                            <div class="endpoint">GET /api/tasks/{id}/status</div>
                            <div class="endpoint">GET /api/tasks/{id}/conversation</div>
                        </div>
                    </div>
                </article>

                <article class="panel full">
                    <div class="panel-head">
                        <h2>Tasks</h2>
                        <span class="chip" id="tasks-count">0</span>
                    </div>
                    <div class="panel-body">
                        <div class="workspace-band" id="tasks-list"></div>
                    </div>
                </article>
            </section>
        </main>
    </div>

    <div class="toast" id="toast" role="status" aria-live="polite"></div>

    <script>
        const state = { agents: [], teams: [], tasks: [] };
        const $ = (selector) => document.querySelector(selector);

        function showToast(message, isError = false) {
            const toast = $('#toast');
            toast.textContent = message;
            toast.className = `toast show${isError ? ' error' : ''}`;
            window.clearTimeout(showToast.timer);
            showToast.timer = window.setTimeout(() => toast.className = 'toast', 3200);
        }

        async function api(path, options = {}) {
            const response = await fetch(path, {
                headers: { 'Content-Type': 'application/json' },
                ...options,
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(data.error || `Request failed: ${response.status}`);
            }
            return data;
        }

        function escapeHtml(value) {
            return String(value)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function renderAgents() {
            $('#metric-agents').textContent = state.agents.length;
            $('#agents-count').textContent = state.agents.length;
            $('#agents-list').innerHTML = state.agents.length
                ? state.agents.map(agent => `
                    <div class="item">
                        <div class="item-title">
                            <strong class="truncate">${escapeHtml(agent.name)}</strong>
                            <span class="chip">${escapeHtml(agent.role)}</span>
                        </div>
                        <div class="meta truncate">${escapeHtml(agent.llm_model)} / temp ${escapeHtml(agent.temperature ?? 0.2)}</div>
                    </div>
                `).join('')
                : '<div class="empty">No agents yet.</div>';
        }

        function renderTeams() {
            $('#metric-teams').textContent = state.teams.length;
            $('#teams-count').textContent = state.teams.length;
            $('#teams-list').innerHTML = state.teams.length
                ? state.teams.map(team => `
                    <div class="item">
                        <div class="item-title">
                            <strong class="truncate">${escapeHtml(team.name)}</strong>
                            <span class="chip">#${escapeHtml(team.id)}</span>
                        </div>
                        <div class="pipeline">
                            ${(team.agent_order || []).map(role => `<span class="chip">${escapeHtml(role)}</span>`).join('')}
                        </div>
                    </div>
                `).join('')
                : '<div class="empty">Create team after adding roles.</div>';

            const teamSelect = $('#task-form select[name="team_id"]');
            teamSelect.innerHTML = state.teams.length
                ? state.teams.map(team => `<option value="${escapeHtml(team.id)}">${escapeHtml(team.name)} (#${escapeHtml(team.id)})</option>`).join('')
                : '<option value="">No teams</option>';
        }

        function renderTasks() {
            $('#metric-tasks').textContent = state.tasks.length;
            $('#tasks-count').textContent = state.tasks.length;
            $('#tasks-list').innerHTML = state.tasks.length
                ? state.tasks.slice().reverse().map(task => `
                    <div class="item">
                        <div class="item-title">
                            <strong class="truncate">${escapeHtml(task.id)}</strong>
                            <span class="chip">${escapeHtml(task.status)}</span>
                        </div>
                        <div class="meta">team ${escapeHtml(task.team_id)} / ${escapeHtml(task.updated_at || task.created_at || '')}</div>
                        <div class="truncate">${escapeHtml(task.input || '')}</div>
                        <div class="actions">
                            <button class="btn secondary" type="button" data-status="${escapeHtml(task.id)}">Poll</button>
                            <button class="btn secondary" type="button" data-convo="${escapeHtml(task.id)}">Conversation</button>
                        </div>
                    </div>
                `).join('')
                : '<div class="empty">Queued tasks appear here.</div>';
        }

        function render() {
            renderAgents();
            renderTeams();
            renderTasks();
        }

        async function refresh() {
            const [agents, teams, tasks] = await Promise.all([
                api('/api/agents'),
                api('/api/teams'),
                api('/api/tasks'),
            ]);
            state.agents = agents.agents || [];
            state.teams = teams.teams || [];
            state.tasks = tasks.tasks || [];
            render();
        }

        function formObject(form) {
            return Object.fromEntries(new FormData(form).entries());
        }

        $('#agent-form').addEventListener('submit', async (event) => {
            event.preventDefault();
            const body = formObject(event.currentTarget);
            body.temperature = Number(body.temperature || 0.2);
            try {
                await api('/api/agents', { method: 'POST', body: JSON.stringify(body) });
                showToast('Agent created.');
                await refresh();
            } catch (error) {
                showToast(error.message, true);
            }
        });

        $('#team-form').addEventListener('submit', async (event) => {
            event.preventDefault();
            const body = formObject(event.currentTarget);
            body.agent_order = body.agent_order.split(',').map(role => role.trim()).filter(Boolean);
            try {
                await api('/api/teams', { method: 'POST', body: JSON.stringify(body) });
                showToast('Team created.');
                await refresh();
            } catch (error) {
                showToast(error.message, true);
            }
        });

        $('#task-form').addEventListener('submit', async (event) => {
            event.preventDefault();
            const body = formObject(event.currentTarget);
            body.team_id = Number(body.team_id);
            body.max_retries = Number(body.max_retries || 0);
            try {
                const task = await api('/api/tasks', { method: 'POST', body: JSON.stringify(body) });
                showToast(`Task queued: ${task.task_id}`);
                await refresh();
            } catch (error) {
                showToast(error.message, true);
            }
        });

        $('#seed-agents').addEventListener('click', async () => {
            const trio = [
                ['Research 1', 'researcher', 'Research thoroughly. Return concise notes.'],
                ['Summary 1', 'summarizer', 'Compress findings into an executive brief.'],
                ['Review 1', 'reviewer', 'Check correctness, risk, and missing evidence.'],
            ];
            try {
                for (const [name, role, system_prompt] of trio) {
                    await api('/api/agents', {
                        method: 'POST',
                        body: JSON.stringify({ name, role, llm_model: 'stub-v1', system_prompt, temperature: 0.2 }),
                    });
                }
                showToast('Agent trio seeded.');
                await refresh();
            } catch (error) {
                showToast(error.message, true);
            }
        });

        $('#refresh').addEventListener('click', () => refresh().catch(error => showToast(error.message, true)));

        $('#tasks-list').addEventListener('click', async (event) => {
            const statusId = event.target.dataset.status;
            const convoId = event.target.dataset.convo;
            try {
                if (statusId) {
                    const result = await api(`/api/tasks/${encodeURIComponent(statusId)}/status`);
                    showToast(`${result.task_id}: ${result.status}`);
                    await refresh();
                }
                if (convoId) {
                    const result = await api(`/api/tasks/${encodeURIComponent(convoId)}/conversation`);
                    const turns = Array.isArray(result.conversation) ? result.conversation.length : 0;
                    showToast(`${result.task_id}: ${turns} conversation turns`);
                }
            } catch (error) {
                showToast(error.message, true);
            }
        });

        refresh().catch(error => {
            render();
            showToast(error.message, true);
        });
    </script>
</body>
</html>
HTML;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = rtrim((string)$uri, '/');
$path = $path === '' ? '/' : $path;

if (($method === 'GET' || $method === 'HEAD') && $path === '/') {
    render_dashboard();
    return;
}

if (($method === 'GET' || $method === 'HEAD') && $path === '/favicon.ico') {
    http_response_code(204);
    return;
}

if (($method === 'GET' || $method === 'HEAD') && $path === '/api') {
    respond([
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
    return;
}

if ($method === 'GET' && $path === '/api/agents') {
    respond(['agents' => store('agents')->all()]);
    return;
}

if ($method === 'GET' && $path === '/api/teams') {
    respond(['teams' => store('teams')->all()]);
    return;
}

if ($method === 'GET' && $path === '/api/tasks') {
    respond(['tasks' => store('tasks')->all()]);
    return;
}

if ($method === 'POST' && $path === '/api/agents') {
    $body = json_body();
    $required = ['name', 'role', 'llm_model', 'system_prompt'];
    foreach ($required as $key) {
        if (!isset($body[$key]) || trim((string)$body[$key]) === '') {
            respond(['error' => "Missing field: {$key}"], 422);
            return;
        }
    }

    $record = [
        'id' => store('agents')->nextId(),
        'name' => trim((string)$body['name']),
        'role' => trim((string)$body['role']),
        'llm_model' => trim((string)$body['llm_model']),
        'system_prompt' => trim((string)$body['system_prompt']),
        'temperature' => (float)($body['temperature'] ?? 0.2),
        'created_at' => date(DATE_ATOM),
    ];

    store('agents')->append($record);
    respond($record, 201);
    return;
}

if ($method === 'POST' && $path === '/api/teams') {
    $body = json_body();
    if (!isset($body['name']) || trim((string)$body['name']) === '') {
        respond(['error' => 'Missing field: name'], 422);
        return;
    }

    $agentOrder = $body['agent_order'] ?? [];
    if (!is_array($agentOrder) || $agentOrder === []) {
        respond(['error' => 'agent_order must be a non-empty array'], 422);
        return;
    }

    $record = [
        'id' => store('teams')->nextId(),
        'name' => trim((string)$body['name']),
        'agent_order' => array_values(array_map(static fn ($v): string => (string)$v, $agentOrder)),
        'created_at' => date(DATE_ATOM),
    ];

    store('teams')->append($record);
    respond($record, 201);
    return;
}

if ($method === 'POST' && $path === '/api/tasks') {
    $body = json_body();
    $teamId = (int)($body['team_id'] ?? 0);
    $input = trim((string)($body['input'] ?? ''));

    if ($teamId <= 0 || $input === '') {
        respond(['error' => 'team_id and input are required'], 422);
        return;
    }

    $team = store('teams')->firstWhere(static fn (array $row): bool => (int)$row['id'] === $teamId);
    if ($team === null) {
        respond(['error' => 'team not found'], 404);
        return;
    }

    $taskId = uniqid('task_', true);
    $agentOrder = $team['agent_order'];
    $firstRole = (string)$agentOrder[0];
    $maxRetries = max(0, (int)($body['max_retries'] ?? 1));

    $task = [
        'id' => $taskId,
        'team_id' => $teamId,
        'input' => $input,
        'status' => 'queued',
        'conversation' => [],
        'result' => null,
        'created_at' => date(DATE_ATOM),
        'updated_at' => date(DATE_ATOM),
    ];

    store('tasks')->append($task);

    sqs()->sendMessage('task-queue', json_encode([
        'task_id' => $taskId,
        'team_id' => $teamId,
        'input' => $input,
        'agent_order' => $agentOrder,
        'max_retries' => $maxRetries,
        'target_role' => $firstRole,
        'conversation' => [],
    ], JSON_UNESCAPED_SLASHES));

    respond([
        'task_id' => $taskId,
        'status' => 'queued',
        'team_id' => $teamId,
    ], 202);
    return;
}

if ($method === 'GET' && preg_match('#^/api/tasks/([^/]+)/status$#', $path, $matches) === 1) {
    $taskId = $matches[1];
    poll_result_queue_for_task($taskId);

    $task = store('tasks')->firstWhere(static fn (array $row): bool => (string)$row['id'] === $taskId);
    if ($task === null) {
        respond(['error' => 'task not found'], 404);
        return;
    }

    respond([
        'task_id' => $task['id'],
        'status' => $task['status'],
        'updated_at' => $task['updated_at'],
    ]);
    return;
}

if ($method === 'GET' && preg_match('#^/api/tasks/([^/]+)/conversation$#', $path, $matches) === 1) {
    $taskId = $matches[1];
    poll_result_queue_for_task($taskId);

    $task = store('tasks')->firstWhere(static fn (array $row): bool => (string)$row['id'] === $taskId);
    if ($task === null) {
        respond(['error' => 'task not found'], 404);
        return;
    }

    respond([
        'task_id' => $taskId,
        'conversation' => $task['conversation'] ?? [],
        'result' => $task['result'],
    ]);
    return;
}

if ($method === 'POST' && $path === '/api/webhooks/n8n') {
    $body = json_body();
    $taskId = (string)($body['task_id'] ?? '');
    if ($taskId === '') {
        respond(['error' => 'task_id is required'], 422);
        return;
    }

    $updated = store('tasks')->updateWhere(
        static fn (array $task): bool => (string)$task['id'] === $taskId,
        static fn (array $task): array => array_merge($task, [
            'status' => (string)($body['status'] ?? 'completed'),
            'conversation' => $body['conversation'] ?? $task['conversation'],
            'result' => $body,
            'updated_at' => date(DATE_ATOM),
        ])
    );

    if ($updated === null) {
        respond(['error' => 'task not found'], 404);
        return;
    }

    respond([
        'message' => 'webhook accepted',
        'task_id' => $taskId,
        'status' => $updated['status'],
    ]);
    return;
}

respond(['error' => 'not found'], 404);
