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
            --primary: #256f67;
            --primary-pressed: #1d5a54;
            --primary-deep: #164842;
            --on-primary: #ffffff;
            --brand-navy: #0a1530;
            --brand-navy-deep: #070f24;
            --brand-navy-mid: #1a2a52;
            --link-blue: #0075de;
            --link-blue-pressed: #005bab;
            --brand-orange: #dd5b00;
            --brand-orange-deep: #793400;
            --brand-pink: #ff64c8;
            --brand-pink-deep: #a02e6d;
            --brand-purple: #7b3ff2;
            --brand-purple-300: #d6b6f6;
            --brand-purple-800: #391c57;
            --brand-teal: #2a9d99;
            --brand-green: #1aae39;
            --brand-yellow: #f5d75e;
            --brand-brown: #523410;
            --card-tint-peach: #ffe8d4;
            --card-tint-rose: #fde0ec;
            --card-tint-mint: #d9f3e1;
            --card-tint-lavender: #e6e0f5;
            --card-tint-sky: #dcecfa;
            --card-tint-yellow: #fef7d6;
            --card-tint-yellow-bold: #f9e79f;
            --card-tint-cream: #f8f5e8;
            --card-tint-gray: #f0eeec;
            --canvas: #ffffff;
            --surface: #f6f5f4;
            --surface-soft: #fafaf9;
            --hairline: #e5e3df;
            --hairline-soft: #ede9e4;
            --hairline-strong: #c8c4be;
            --ink-deep: #111111;
            --ink: #1a1a1a;
            --charcoal: #37352f;
            --slate: #5d5b54;
            --steel: #787671;
            --stone: #a4a097;
            --muted: #bbb8b1;
            --on-dark: #ffffff;
            --on-dark-muted: #a4a097;
            --semantic-success: #1aae39;
            --semantic-warning: #dd5b00;
            --semantic-error: #e03131;
            --font-sans: Satoshi, Geist, "Aptos", -apple-system, system-ui, "Segoe UI", Helvetica, sans-serif;
            --font-mono: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            --spacing-xxs: 4px;
            --spacing-xs: 8px;
            --spacing-sm: 12px;
            --spacing-md: 16px;
            --spacing-lg: 20px;
            --spacing-xl: 24px;
            --spacing-xxl: 32px;
            --spacing-xxxl: 40px;
            --spacing-section: 64px;
            --spacing-section-lg: 96px;
            --radius-xs: 4px;
            --radius-sm: 6px;
            --radius-md: 8px;
            --radius-lg: 12px;
            --radius-xl: 16px;
            --radius-full: 9999px;
            --shadow-card: rgba(15, 15, 15, 0.08) 0px 4px 12px 0px;
            --shadow-mockup: rgba(15, 15, 15, 0.20) 0px 24px 48px -8px;
            --shadow-subtle: rgba(15, 15, 15, 0.04) 0px 1px 2px 0px;
            --page-max-width: 1280px;
        }

        * { box-sizing: border-box; }

        html {
            color: var(--ink);
            background: var(--canvas);
            font-family: var(--font-sans);
            letter-spacing: 0;
        }

        body { margin: 0; min-height: 100vh; }

        button, input, textarea, select { font: inherit; }
        button { cursor: pointer; }

        .app {
            display: grid;
            grid-template-columns: 240px minmax(0, 1fr);
            min-height: 100vh;
            max-width: 1480px;
            margin: 0 auto;
        }

        .rail {
            position: sticky;
            top: 0;
            height: 100vh;
            border-right: 1px solid var(--hairline);
            background: var(--canvas);
            padding: var(--spacing-xl);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            margin-bottom: var(--spacing-xxl);
        }

        .mark {
            display: grid;
            place-items: center;
            width: 32px;
            height: 32px;
            border-radius: var(--radius-sm);
            background: var(--primary);
            color: var(--on-primary);
            font-family: var(--font-mono);
            font-size: 12px;
            font-weight: 600;
        }

        .brand strong, .panel h2 { margin: 0; font-weight: 600; }
        .brand strong { font-size: 16px; color: var(--ink); }

        .brand span, .eyebrow, .meta, .field span, .chip, .endpoint {
            font-family: var(--font-mono);
            font-size: 12px;
        }

        .brand span, .meta, .field span { color: var(--steel); }

        .nav { display: grid; gap: var(--spacing-xxs); }

        .nav a {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 32px;
            padding: var(--spacing-xs) var(--spacing-sm);
            color: var(--charcoal);
            text-decoration: none;
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 500;
        }

        .nav a:hover, .nav a:focus-visible, .nav a.active {
            background: var(--surface);
            outline: none;
        }

        .rail-note {
            position: absolute;
            left: var(--spacing-xl);
            right: var(--spacing-xl);
            bottom: var(--spacing-xl);
            padding: var(--spacing-md);
            border: 1px solid var(--hairline);
            border-radius: var(--radius-md);
            background: var(--surface);
        }

        .main { padding: var(--spacing-xl); }

        .eyebrow {
            color: var(--primary);
            font-weight: 600;
            letter-spacing: 1px;
            margin-bottom: var(--spacing-sm);
            text-transform: uppercase;
        }

        .status-row {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: var(--spacing-sm);
            padding: var(--spacing-sm) var(--spacing-md);
            border-top: 1px solid var(--hairline-soft);
            font-size: 14px;
        }

        .status-row:first-child { border-top: 0; }

        .chip {
            display: inline-flex;
            align-items: center;
            min-height: 24px;
            padding: 2px 8px;
            border: 1px solid var(--hairline-strong);
            border-radius: var(--radius-xs);
            background: transparent;
            color: var(--steel);
            white-space: nowrap;
            font-size: 12px;
        }

        .chip.hot {
            color: var(--semantic-success);
            border-color: var(--semantic-success);
            background: transparent;
        }

        .page { display: none; }
        .page.active { display: block; }

        .grid {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: var(--spacing-md);
            margin-top: 0;
        }

        .page:not(.active) { display: none; }
        .page.grid.active { display: grid; }

        .panel {
            min-width: 0;
            border-radius: var(--radius-md);
            background: var(--canvas);
            border: 1px solid var(--hairline);
            overflow: hidden;
        }

        .panel.large { grid-column: span 8; }
        .panel.small { grid-column: span 4; }
        .panel.full { grid-column: 1 / -1; }
        .panel.clean { border-color: var(--hairline-soft); }

        .panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--spacing-sm);
            min-height: 44px;
            padding: var(--spacing-sm) var(--spacing-md);
            border-bottom: 1px solid var(--hairline);
        }

        .panel h2 {
            font-size: 16px;
            line-height: 1.4;
            font-weight: 600;
        }

        .panel-body { padding: var(--spacing-md); }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: var(--spacing-md);
        }

        .field { display: grid; gap: var(--spacing-xs); }
        .field.wide { grid-column: 1 / -1; }

        input, textarea, select {
            width: 100%;
            min-height: 40px;
            border: 1px solid var(--hairline-strong);
            border-radius: var(--radius-md);
            background: var(--canvas);
            color: var(--ink);
            padding: var(--spacing-sm) var(--spacing-md);
            outline: none;
            font-size: 14px;
            line-height: 1.55;
        }

        textarea { min-height: 92px; resize: vertical; line-height: 1.55; }
        select[multiple] { min-height: 116px; }

        input:focus, textarea:focus, select:focus {
            border: 2px solid var(--primary);
            padding: calc(var(--spacing-sm) - 1px) calc(var(--spacing-md) - 1px);
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: var(--spacing-xs);
            margin-top: var(--spacing-md);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-xs);
            min-height: 36px;
            padding: 10px 18px;
            border: 1px solid var(--hairline-strong);
            border-radius: var(--radius-md);
            background: transparent;
            color: var(--ink);
            font-size: 14px;
            font-weight: 500;
            line-height: 1.3;
        }

        .btn.primary {
            background: var(--primary);
            border-color: var(--primary);
            color: var(--on-primary);
        }

        .btn.primary:hover, .btn.primary:focus-visible {
            background: var(--primary-pressed);
            border-color: var(--primary-pressed);
            outline: none;
        }

        .btn.secondary {
            border-color: var(--hairline-strong);
            color: var(--ink);
        }

        .btn:hover, .btn:focus-visible {
            background: var(--surface);
            outline: none;
        }

        .btn:disabled { cursor: not-allowed; opacity: 0.56; }

        .list { display: grid; gap: var(--spacing-xs); }

        .item {
            display: grid;
            gap: var(--spacing-xs);
            padding: var(--spacing-md);
            border: 1px solid var(--hairline);
            border-radius: var(--radius-md);
            background: var(--canvas);
        }

        .item-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--spacing-sm);
            min-width: 0;
        }

        .item-title strong { font-weight: 600; }

        .item-title strong, .truncate {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .pipeline, .drag-palette, .drop-zone {
            display: flex;
            flex-wrap: wrap;
            gap: var(--spacing-xs);
        }

        .drag-palette, .drop-zone {
            min-height: 46px;
            padding: var(--spacing-xs);
            border: 1px dashed var(--hairline-strong);
            border-radius: var(--radius-md);
            background: var(--surface-soft);
        }

        .drop-zone.drag-over {
            border-color: var(--primary);
            box-shadow: inset 0 0 0 1px var(--primary);
        }

        .role-chip { cursor: grab; user-select: none; }
        .role-chip:active { cursor: grabbing; }

        .chip button {
            margin-left: var(--spacing-xs);
            border: 0;
            background: transparent;
            color: inherit;
            padding: 0;
            line-height: 1;
        }

        .pipeline .chip:nth-child(3n+1) { border-color: var(--brand-pink); color: var(--brand-pink-deep); }
        .pipeline .chip:nth-child(3n+2) { border-color: var(--brand-teal); color: var(--brand-teal); }
        .pipeline .chip:nth-child(3n+3) { border-color: var(--brand-purple); color: var(--brand-purple); }

        .empty {
            min-height: 126px;
            display: grid;
            place-items: center;
            color: var(--muted);
            text-align: center;
            border: 1px dashed var(--hairline);
            border-radius: var(--radius-md);
            background: var(--surface-soft);
            font-size: 14px;
        }

        .toast {
            position: fixed;
            right: var(--spacing-xl);
            bottom: var(--spacing-xl);
            z-index: 10;
            display: none;
            max-width: min(420px, calc(100vw - 48px));
            padding: var(--spacing-md);
            border: 1px solid var(--hairline);
            border-radius: var(--radius-md);
            background: var(--canvas);
            color: var(--ink);
            box-shadow: var(--shadow-mockup);
            font-size: 14px;
        }

        .toast.show { display: block; }
        .toast.error { border-color: var(--semantic-error); color: var(--semantic-error); }

        .endpoint {
            overflow-x: auto;
            padding: var(--spacing-sm) var(--spacing-md);
            border: 1px solid var(--hairline);
            border-radius: var(--radius-sm);
            background: var(--surface-soft);
            color: var(--steel);
            white-space: nowrap;
            font-size: 13px;
        }

        .workspace-band {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: var(--spacing-xs);
        }

        .teams-layout { display: grid; gap: var(--spacing-md); }
        .canvas-shell { display: grid; gap: var(--spacing-md); }
        .canvas-controls {
            display: grid;
            grid-template-columns: minmax(180px, 280px) minmax(0, 1fr) auto;
            gap: var(--spacing-sm);
            align-items: end;
        }

        .node-board {
            min-height: 620px;
            position: relative;
            overflow: auto;
            border: 1px solid #1f1f1f;
            border-radius: var(--radius-lg);
            background:
                radial-gradient(circle at 50% 10%, rgba(86, 69, 212, 0.14), transparent 28%),
                linear-gradient(#111 1px, transparent 1px),
                linear-gradient(90deg, #111 1px, transparent 1px),
                #080808;
            background-size: auto, 48px 48px, 48px 48px, auto;
            color: #f3f3f3;
            padding: var(--spacing-lg);
        }

        .node-board.compact {
            min-height: auto;
            padding: var(--spacing-sm);
        }
        .node-board.orchestration {
            min-height: calc(100dvh - 190px);
        }
        #agent-order-input { display: none; }

        .node-toolbar {
            position: sticky;
            top: 0;
            z-index: 2;
            display: flex;
            justify-content: flex-end;
            gap: 6px;
            margin-bottom: var(--spacing-xl);
        }

        .node-tool {
            width: 26px;
            height: 26px;
            border: 1px solid #252525;
            border-radius: var(--radius-xs);
            background: #0d0d0d;
            color: #d9d9d9;
            font-family: var(--font-mono);
            font-size: 11px;
        }

        .node-canvas {
            position: relative;
            min-width: 760px;
            min-height: 420px;
            border: 1px solid #1f1f1f;
            border-radius: var(--radius-md);
            background: rgba(255, 255, 255, 0.02);
        }

        .node-canvas.builder {
            min-width: 0;
            min-height: calc(100dvh - 290px);
            overflow: hidden;
        }
        .node-canvas.saved {
            min-height: 320px;
            margin-top: var(--spacing-md);
        }

        .node-canvas.drag-over {
            border-color: var(--brand-yellow);
            box-shadow: inset 0 0 0 1px var(--brand-yellow);
        }

        .node-edges {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }

        .node-edges path {
            fill: none;
            stroke: #4b4b4b;
            stroke-width: 2;
        }

        .node {
            position: absolute;
            z-index: 1;
            display: grid;
            grid-template-columns: 34px minmax(0, 1fr);
            gap: var(--spacing-sm);
            width: 210px;
            padding: var(--spacing-md);
            border: 1px solid #2a2a2a;
            border-radius: var(--radius-md);
            background: #171717;
            color: #f7f7f7;
            box-shadow: 0 18px 50px rgba(0, 0, 0, 0.24);
            cursor: grab;
            user-select: none;
        }

        .node:active { cursor: grabbing; }

        .node-icon {
            display: grid;
            place-items: center;
            width: 32px;
            height: 32px;
            border-radius: var(--radius-full);
            background: #242424;
            color: #bdbdbd;
            font-size: 15px;
        }

        .node-title strong {
            display: block;
            overflow: hidden;
            color: #f7f7f7;
            font-size: 14px;
            font-weight: 600;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .node-title span {
            display: block;
            overflow: hidden;
            color: #8a8a8a;
            font-size: 11px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .node-remove {
            position: absolute;
            top: 6px;
            right: 6px;
            display: grid;
            place-items: center;
            width: 18px;
            height: 18px;
            border: 1px solid #333;
            border-radius: var(--radius-full);
            background: #101010;
            color: #999;
            padding: 0;
            font-size: 12px;
            line-height: 1;
        }

        .node-empty {
            position: absolute;
            inset: var(--spacing-xl);
            display: grid;
            place-items: center;
            color: #777;
            text-align: center;
            border: 1px dashed #2a2a2a;
            border-radius: var(--radius-md);
            pointer-events: none;
        }

        .flow-team {
            min-width: 760px;
            display: grid;
            gap: var(--spacing-md);
            margin-bottom: var(--spacing-xxl);
        }

        .flow-team-name {
            color: #777;
            font-family: var(--font-mono);
            font-size: 12px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .node-agent-dot {
            display: inline-block;
            width: 7px;
            height: 7px;
            margin-right: 5px;
            border-radius: var(--radius-full);
            background: var(--brand-yellow);
        }

        .flow-actions {
            display: flex;
            justify-content: flex-end;
            width: 100%;
        }

        .skills-layout {
            display: grid;
            grid-template-columns: minmax(280px, 360px) minmax(0, 1fr);
            gap: var(--spacing-md);
        }

        .skill-result {
            display: grid;
            gap: var(--spacing-xs);
            padding: var(--spacing-sm);
            border: 1px solid var(--hairline);
            border-radius: var(--radius-sm);
            background: var(--surface-soft);
        }

        .demo-output { display: grid; gap: var(--spacing-md); }

        .demo-summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: var(--spacing-xs);
        }

        .summary-cell {
            display: grid;
            gap: var(--spacing-xxs);
            padding: var(--spacing-md);
            border: 1px solid var(--hairline);
            border-radius: var(--radius-md);
            background: var(--canvas);
            min-width: 0;
        }

        .summary-cell span { color: var(--steel); font-size: 12px; }
        .summary-cell strong {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-weight: 600;
        }

        .conversation { display: grid; gap: var(--spacing-xs); }

        .turn {
            display: grid;
            grid-template-columns: 140px minmax(0, 1fr);
            gap: var(--spacing-md);
            padding: var(--spacing-md);
            border: 1px solid var(--hairline);
            border-radius: var(--radius-md);
            background: var(--canvas);
        }

        .turn-role { display: grid; align-content: start; gap: var(--spacing-xs); }
        .turn-role strong { color: var(--primary); font-weight: 600; }
        .turn-role span, .result-block span { color: var(--steel); font-size: 12px; }

        .turn-content, .result-block pre {
            margin: 0;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
            color: var(--ink);
            font-family: var(--font-mono);
            font-size: 13px;
            line-height: 1.5;
        }

        .result-block {
            display: grid;
            gap: var(--spacing-xs);
            padding: var(--spacing-md);
            border: 1px solid var(--hairline);
            border-radius: var(--radius-md);
            background: var(--canvas);
        }

        @media (max-width: 980px) {
            .app { grid-template-columns: 1fr; }
            .rail { position: relative; height: auto; border-right: 0; border-bottom: 1px solid var(--hairline); }
            .rail-note { position: static; margin-top: var(--spacing-md); }
            .form-grid, .workspace-band, .demo-summary, .turn, .canvas-controls, .skills-layout { grid-template-columns: 1fr; }
            .panel.large, .panel.small { grid-column: 1 / -1; }
        }

        @media (max-width: 620px) {
            .main { padding: var(--spacing-md); }
            .panel-head, .item-title { align-items: flex-start; flex-direction: column; }
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
                <a href="#compose" data-page-link="compose" class="active">Compose Agent <span>01</span></a>
                <a href="#teams" data-page-link="teams">Teams <span>02</span></a>
                <a href="#skills" data-page-link="skills">Skills <span>03</span></a>
                <a href="#tasks" data-page-link="tasks">Tasks <span>04</span></a>
            </nav>
            <div class="rail-note">
                <div class="meta">Local stack</div>
                <div class="status-row"><span>agents</span><strong id="metric-agents">0</strong></div>
                <div class="status-row"><span>teams</span><strong id="metric-teams">0</strong></div>
                <div class="status-row"><span>tasks</span><strong id="metric-tasks">0</strong></div>
            </div>
        </aside>

        <main class="main">
            <section class="page active grid" id="compose">
                <article class="panel clean large">
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
                                        <option value="architect">architect</option>
                                        <option value="frontend-engineer">frontend-engineer</option>
                                        <option value="backend-engineer">backend-engineer</option>
                                        <option value="devops-engineer">devops-engineer</option>
                                        <option value="security-reviewer">security-reviewer</option>
                                        <option value="performance-engineer">performance-engineer</option>
                                        <option value="qa-engineer">qa-engineer</option>
                                        <option value="technical-writer">technical-writer</option>
                                    </select>
                                </label>
                                <label class="field wide">
                                    <span>System Prompt Directory</span>
                                    <select id="prompt-select" aria-label="Popular system prompt directory">
                                        <option value="">Select a popular system prompt...</option>
                                    </select>
                                </label>
                                <label class="field wide">
                                    <span>Skills</span>
                                    <select id="skills-picker" name="skill_ids" multiple aria-label="Select skills to attach">
                                        <option value="" disabled>Loading skills...</option>
                                    </select>
                                    <div class="meta">Select multiple skills to attach to this agent</div>
                                </label>
                                <label class="field wide">
                                    <span>Model</span>
                                    <select name="llm_model" id="model-select" required>
                                        <option value="">Select model...</option>
                                        <optgroup label="NVIDIA">
                                            <option value="nvidia/llama-3.1-nemotron-70b-instruct">NVIDIA Llama 3.1 Nemotron 70B</option>
                                            <option value="nvidia/llama-3.1-nemotron-51b-instruct">NVIDIA Llama 3.1 Nemotron 51B</option>
                                            <option value="nvidia/llama-3.3-nemotron-super-49b-v1">NVIDIA Llama 3.3 Nemotron 49B</option>
                                            <option value="nvidia/nemotron-4-340b-instruct">NVIDIA Nemotron 4 340B</option>
                                            <option value="nvidia/nemotron-mini-4b-instruct">NVIDIA Nemotron Mini 4B</option>
                                            <option value="nvidia/nemotron-3-nano-30b-a3b">NVIDIA Nemotron 3 Nano 30B</option>
                                            <option value="nvidia/mistral-nemo-minitron-8b-8k-instruct">NVIDIA Mistral NeMo Minitron 8B</option>
                                            <option value="nvidia/neva-22b">NVIDIA NeVA 22B</option>
                                        </optgroup>
                                        <optgroup label="Meta">
                                            <option value="meta/llama-3.1-8b-instruct">Meta Llama 3.1 8B</option>
                                            <option value="meta/llama-3.1-70b-instruct">Meta Llama 3.1 70B</option>
                                            <option value="meta/llama-3.3-70b-instruct">Meta Llama 3.3 70B</option>
                                            <option value="meta/llama-3.2-11b-vision-instruct">Meta Llama 3.2 11B Vision</option>
                                        </optgroup>
                                        <optgroup label="Mistral">
                                            <option value="mistralai/mixtral-8x7b-instruct-v0.1">Mistral Mixtral 8x7B</option>
                                            <option value="mistralai/mistral-7b-instruct-v0.3">Mistral 7B Instruct v0.3</option>
                                            <option value="mistralai/mistral-large-2-instruct">Mistral Large 2</option>
                                        </optgroup>
                                        <optgroup label="Other">
                                            <option value="custom">Custom model ID...</option>
                                        </optgroup>
                                    </select>
                                </label>
                                <label class="field" id="custom-model-field" style="display:none">
                                    <span>Custom Model ID</span>
                                    <input name="llm_model_custom" placeholder="nvidia/model-name">
                                </label>
                                <label class="field wide">
                                    <span>System Prompt</span>
                                    <textarea name="system_prompt" required>Research thoroughly. Return crisp notes and sources when available.</textarea>
                                </label>
                            </div>
                            <div class="actions">
                                <button class="btn" type="submit">Create agent</button>
                            </div>
                        </form>
                    </div>
                </article>

                <article class="panel clean small">
                    <div class="panel-head">
                        <h2>Agents</h2>
                        <span class="chip" id="agents-count">0</span>
                    </div>
                    <div class="panel-body">
                        <div class="list" id="agents-list"></div>
                    </div>
                </article>

                <article class="panel clean full" id="terminal-bench">
                    <div class="panel-head">
                        <h2>Terminal-Bench</h2>
                        <span class="chip" id="bench-status">ready</span>
                    </div>
                    <div class="panel-body">
                        <form id="bench-form">
                            <div class="form-grid">
                                <label class="field">
                                    <span>Agent</span>
                                    <select name="agent_id" required></select>
                                </label>
                                <label class="field">
                                    <span>Dataset or Local Task</span>
                                    <input name="dataset" value="benchmarks/harbor/tasks/agentforge-smoke" required>
                                </label>
                                <label class="field">
                                    <span>Task ID (optional)</span>
                                    <input name="task" placeholder="adaptive-rejection-sampler">
                                </label>
                                <label class="field">
                                    <span>Timeout Seconds</span>
                                    <input name="timeout_sec" type="number" value="3600" min="60">
                                </label>
                            </div>
                            <div class="actions">
                                <button class="btn" type="submit">Run Terminal-Bench</button>
                                <button class="btn secondary" type="button" id="refresh-bench">Refresh runs</button>
                            </div>
                        </form>
                        <div class="demo-output" id="bench-output" style="margin-top: var(--spacing-12);">
                            <div class="empty">Run the smoke task or a Terminal-Bench task from the UI.</div>
                        </div>
                    </div>
                </article>

            </section>

            <section class="page" id="teams">
                <div class="teams-layout">
                <article class="panel clean">
                    <div class="panel-head">
                        <h2>Teams</h2>
                        <span class="chip" id="teams-count">0</span>
                    </div>
                    <div class="panel-body">
                        <form id="team-form" class="canvas-shell">
                            <div class="canvas-controls">
                                <label class="field">
                                    <span>Name</span>
                                    <input name="name" value="Default Team" required>
                                </label>
                                <label class="field">
                                    <span>Available Agents</span>
                                    <div class="drag-palette" id="agent-palette"></div>
                                </label>
                                <div class="actions">
                                    <button class="btn" type="submit">Create team</button>
                                </div>
                            </div>
                            <div class="node-board orchestration">
                                <div class="node-toolbar">
                                    <button class="node-tool" type="button" title="Zoom in">+</button>
                                    <button class="node-tool" type="button" title="Zoom out">-</button>
                                    <button class="node-tool" type="button" title="Fit canvas">Fit</button>
                                </div>
                                        <div class="node-canvas builder" id="agent-node-canvas" aria-label="Agent orchestration canvas">
                                            <svg class="node-edges" id="agent-node-edges" aria-hidden="true"></svg>
                                            <div class="node-empty" id="agent-node-empty">Drop agents here. Drag nodes to set execution order left to right.</div>
                                        </div>
                                <input name="agent_order" id="agent-order-input" value="" required>
                                <div class="node-canvas saved" id="teams-list"></div>
                            </div>
                        </form>
                    </div>
                </article>
                </div>
            </section>

            <section class="page" id="skills">
                <div class="skills-layout">
                <article class="panel clean small">
                    <div class="panel-head">
                        <h2>Import</h2>
                        <span class="chip">skills.sh</span>
                    </div>
                    <div class="panel-body">
                        <label class="field wide">
                            <span>Search skills.sh</span>
                            <input id="skills-sh-search" type="search" placeholder="react, security, pdf">
                        </label>
                        <div id="skills-sh-results"></div>
                    </div>
                </article>
                <article class="panel clean large">
                    <div class="panel-head">
                        <h2>Skills</h2>
                        <span class="chip">GET /api/skills</span>
                    </div>
                    <div class="panel-body">
                        <div id="skills-list"></div>
                    </div>
                </article>
                <article class="panel clean small">
                    <div class="panel-head">
                        <h2>Create Skill</h2>
                        <span class="chip">POST /api/skills</span>
                    </div>
                    <div class="panel-body">
                        <form id="skill-form">
                            <div class="form-grid">
                                <label class="field wide">
                                    <span>Name</span>
                                    <input name="name" required>
                                </label>
                                <label class="field wide">
                                    <span>Description</span>
                                    <textarea name="description" required></textarea>
                                </label>
                                <label class="field wide">
                                    <span>Body (Markdown with YAML frontmatter)</span>
                                    <textarea name="body" required rows="10" placeholder="# Skill Name&#10;&#10;Description of what this skill does..."></textarea>
                                </label>
                            </div>
                            <div class="actions">
                                <button class="btn" type="submit">Create skill</button>
                            </div>
                        </form>
                    </div>
                </article>
                </div>
            </section>

            <section class="page grid" id="tasks">
                <article class="panel large">
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

                <article class="panel full" id="output">
                    <div class="panel-head">
                        <h2>Demo Output</h2>
                        <span class="chip" id="output-status">waiting</span>
                    </div>
                    <div class="panel-body">
                        <div class="demo-output" id="demo-output">
                            <div class="empty">Queue a task, then open Conversation to see what each agent did.</div>
                        </div>
                    </div>
                </article>
            </section>
        </main>
    </div>

    <div class="toast" id="toast" role="status" aria-live="polite"></div>

    <script>
        const state = { agents: [], teams: [], tasks: [], skills: [], benchRuns: [], selectedBenchRun: null, selectedOutput: null, teamBuilderNodes: [] };
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
                        <div class="meta truncate">${escapeHtml(agent.llm_model)}</div>
                        <div class="actions">
                            <button class="btn secondary" type="button" data-delete-agent="${escapeHtml(agent.id)}">Delete</button>
                        </div>
                    </div>
                `).join('')
                : '<div class="empty">No agents yet.</div>';
            const benchAgentSelect = $('#bench-form select[name="agent_id"]');
            if (benchAgentSelect) {
                benchAgentSelect.innerHTML = state.agents.length
                    ? state.agents.map(agent => `<option value="${escapeHtml(agent.id)}">${escapeHtml(agent.name)} / ${escapeHtml(agent.role)} (#${escapeHtml(agent.id)})</option>`).join('')
                    : '<option value="">No agents</option>';
            }
            renderAgentOrderBuilder();
        }

        function teamOrderRoles() {
            if (state.teamBuilderNodes.length) {
                return orderedBuilderNodes().map(node => node.role);
            }
            return $('#agent-order-input').value.split(',').map(role => role.trim()).filter(Boolean);
        }

        function setTeamOrderRoles(roles) {
            state.teamBuilderNodes = roles.map((role, index) => ({
                id: `builder-${Date.now()}-${index}`,
                role,
                x: 32 + (index * 240),
                y: 120 + ((index % 2) * 82),
            }));
            syncTeamOrderInput();
            renderAgentOrderBuilder();
        }

        function orderedBuilderNodes() {
            return state.teamBuilderNodes.slice().sort((a, b) => (a.x - b.x) || (a.y - b.y));
        }

        function syncTeamOrderInput() {
            $('#agent-order-input').value = orderedBuilderNodes().map(node => node.role).join(', ');
        }

        function roleAgent(role) {
            return state.agents.find(item => item.role === role) || {};
        }

        function renderAgentOrderBuilder() {
            const palette = $('#agent-palette');
            const canvas = $('#agent-node-canvas');
            if (!palette || !canvas) {
                return;
            }

            const uniqueRoles = [...new Set(state.agents.map(agent => agent.role))];
            palette.innerHTML = uniqueRoles.length
                ? uniqueRoles.map(role => `<span class="chip role-chip" draggable="true" data-role="${escapeHtml(role)}">${escapeHtml(role)}</span>`).join('')
                : '<span class="meta">Seed agents first, or type roles below.</span>';

            if (!state.teamBuilderNodes.length) {
                const roles = $('#agent-order-input').value.split(',').map(role => role.trim()).filter(Boolean);
                state.teamBuilderNodes = roles.map((role, index) => ({
                    id: `builder-${Date.now()}-${index}`,
                    role,
                    x: 32 + (index * 240),
                    y: 120 + ((index % 2) * 82),
                }));
            }
            syncTeamOrderInput();
            const edges = $('#agent-node-edges');
            const empty = $('#agent-node-empty');
            canvas.querySelectorAll('.node').forEach(node => node.remove());
            const nodes = orderedBuilderNodes();
            empty.style.display = nodes.length ? 'none' : 'grid';
            edges.innerHTML = renderNodeEdges(nodes);
            nodes.forEach((node, index) => {
                canvas.insertAdjacentHTML('beforeend', renderNode(node, index, true));
            });
        }

        function roleIcon(role) {
            const normalized = String(role || '').toLowerCase();
            if (normalized.includes('cto') || normalized.includes('engineer') || normalized.includes('code')) {
                return '‹›';
            }
            if (normalized.includes('cmo') || normalized.includes('market')) {
                return '◉';
            }
            if (normalized.includes('ceo') || normalized.includes('lead') || normalized.includes('planner')) {
                return '♛';
            }
            if (normalized.includes('research')) {
                return '⌕';
            }
            if (normalized.includes('review') || normalized.includes('qa')) {
                return '✓';
            }
            return '◇';
        }

        function renderNodeEdges(nodes) {
            return nodes.slice(1).map((node, index) => {
                const prev = nodes[index];
                const startX = prev.x + 210;
                const startY = prev.y + 42;
                const endX = node.x;
                const endY = node.y + 42;
                const midX = startX + Math.max(40, (endX - startX) / 2);
                return `<path d="M ${startX} ${startY} C ${midX} ${startY}, ${midX} ${endY}, ${endX} ${endY}" />`;
            }).join('');
        }

        function renderNode(node, index, editable = false) {
            const role = node.role;
            const agent = roleAgent(role);
            return `
                <div class="node" ${editable ? 'draggable="true"' : ''} data-node-id="${escapeHtml(node.id)}" style="left: ${Number(node.x)}px; top: ${Number(node.y)}px;">
                    ${editable ? `<button class="node-remove" type="button" aria-label="Remove ${escapeHtml(role)}" data-remove-node="${escapeHtml(node.id)}">×</button>` : ''}
                    <div class="node-icon">${escapeHtml(roleIcon(role))}</div>
                    <div class="node-title">
                        <strong>${escapeHtml(role)}</strong>
                        <span>${escapeHtml(agent.name || `Agent ${index + 1}`)}</span>
                        <span><i class="node-agent-dot"></i>${escapeHtml(agent.llm_model || 'unassigned model')}</span>
                    </div>
                </div>
            `;
        }

        function teamNodes(team) {
            const roles = team.agent_order || [];
            const saved = Array.isArray(team.nodes) ? team.nodes : [];
            if (saved.length) {
                return saved.map((node, index) => ({
                    id: String(node.id || `team-${team.id}-${index}`),
                    role: String(node.role || roles[index] || `agent-${index + 1}`),
                    x: Number(node.x ?? (32 + index * 240)),
                    y: Number(node.y ?? (120 + (index % 2) * 82)),
                }));
            }
            return roles.map((role, index) => ({
                id: `team-${team.id}-${index}`,
                role,
                x: 32 + (index * 240),
                y: 120 + ((index % 2) * 82),
            }));
        }

        function renderTeams() {
            $('#metric-teams').textContent = state.teams.length;
            $('#teams-count').textContent = state.teams.length;
            $('#teams-list').innerHTML = state.teams.length
                ? state.teams.map(team => {
                    const nodes = teamNodes(team);
                    return `
                        <div class="flow-team">
                            <div class="flow-team-name">${escapeHtml(team.name)} / #${escapeHtml(team.id)}</div>
                            <div class="node-canvas">
                                <svg class="node-edges" aria-hidden="true">${renderNodeEdges(nodes)}</svg>
                                ${nodes.map((node, index) => renderNode(node, index)).join('')}
                            </div>
                            <div class="flow-actions">
                                <button class="btn secondary" type="button" data-delete-team="${escapeHtml(team.id)}">Delete team</button>
                            </div>
                        </div>
                    `;
                }).join('')
                : '<div class="empty">Create team after adding roles.</div>';

            const teamOptions = state.teams.length
                ? state.teams.map(team => `<option value="${escapeHtml(team.id)}">${escapeHtml(team.name)} (#${escapeHtml(team.id)})</option>`).join('')
                : '<option value="">No teams</option>';
            $('#task-form select[name="team_id"]').innerHTML = teamOptions;
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
                            <button class="btn secondary" type="button" data-delete-task="${escapeHtml(task.id)}">Delete</button>
                        </div>
                    </div>
                `).join('')
                : '<div class="empty">Queued tasks appear here.</div>';
        }

        function roleDescription(role) {
            return {
                researcher: 'Researches the original task input.',
                summarizer: 'Condenses prior findings into a clearer brief.',
                reviewer: 'Reviews the final summary for quality and risk.',
            }[role] || 'Adds a workflow turn.';
        }

        function renderDemoOutput() {
            const output = state.selectedOutput;
            const status = $('#output-status');
            const target = $('#demo-output');
            if (!output) {
                status.textContent = 'waiting';
                target.innerHTML = '<div class="empty">Queue a task, then open Conversation to see what each agent did.</div>';
                return;
            }

            const conversation = Array.isArray(output.conversation) ? output.conversation : [];
            status.textContent = output.status || 'loaded';
            const turns = conversation.length
                ? conversation.map((turn, index) => `
                    <div class="turn">
                        <div class="turn-role">
                            <strong>${escapeHtml(turn.role || `turn ${index + 1}`)}</strong>
                            <span>${escapeHtml(roleDescription(turn.role || ''))}</span>
                            <span>${escapeHtml(turn.timestamp ? new Date(Number(turn.timestamp) * 1000).toLocaleTimeString() : '')}</span>
                        </div>
                        <pre class="turn-content">${escapeHtml(turn.content || '')}</pre>
                    </div>
                `).join('')
                : '<div class="empty">No agent turns have been recorded yet. Poll again in a moment.</div>';

            const result = output.result ? JSON.stringify(output.result, null, 2) : 'No final result payload yet.';
            target.innerHTML = `
                <div class="demo-summary">
                    <div class="summary-cell"><span>task</span><strong>${escapeHtml(output.task_id || '')}</strong></div>
                    <div class="summary-cell"><span>status</span><strong>${escapeHtml(output.status || '')}</strong></div>
                    <div class="summary-cell"><span>turns</span><strong>${conversation.length}</strong></div>
                    <div class="summary-cell"><span>workflow</span><strong>research -> summarize -> review</strong></div>
                </div>
                <div class="result-block">
                    <span>Input</span>
                    <pre>${escapeHtml(output.input || '')}</pre>
                </div>
                <div class="conversation">${turns}</div>
                <div class="result-block">
                    <span>Final payload</span>
                    <pre>${escapeHtml(result)}</pre>
                </div>
            `;
        }

        function renderBenchOutput() {
            const status = $('#bench-status');
            const target = $('#bench-output');
            if (!target) {
                return;
            }

            const selected = state.selectedBenchRun;
            status.textContent = selected?.status || (state.benchRuns.length ? 'runs loaded' : 'ready');
            const runs = state.benchRuns.length
                ? state.benchRuns.slice().reverse().map(run => `
                    <div class="item">
                        <div class="item-title">
                            <strong class="truncate">${escapeHtml(run.id)}</strong>
                            <span class="chip">${escapeHtml(run.status)}</span>
                        </div>
                        <div class="meta truncate">${escapeHtml((run.command || []).join(' '))}</div>
                        <div class="actions">
                            <button class="btn secondary" type="button" data-bench-run="${escapeHtml(run.id)}">Open logs</button>
                        </div>
                    </div>
                `).join('')
                : '<div class="empty">No Terminal-Bench runs yet.</div>';

            const logs = selected ? `
                <div class="result-block">
                    <span>Run ${escapeHtml(selected.id)} / ${escapeHtml(selected.status)}</span>
                    <pre>${escapeHtml(selected.stdout_tail || '(no stdout yet)')}</pre>
                </div>
                <div class="result-block">
                    <span>stderr</span>
                    <pre>${escapeHtml(selected.stderr_tail || '(no stderr)')}</pre>
                </div>
            ` : '';

            target.innerHTML = `<div class="workspace-band">${runs}</div>${logs}`;
        }

        function renderSkillResults() {
            const target = $('#skill-select');
            if (!target) {
                return;
            }

            target.innerHTML = state.skills.length
                ? '<option value="">Select a skill to attach...</option>' + state.skills.map(skill => `
                    <option value="${escapeHtml(skill.id)}">
                        ${escapeHtml(skill.title || skill.id)} · ${escapeHtml(skill.source || 'skills.sh')} · ${escapeHtml(skill.install_count || 0)} installs
                    </option>
                `).join('')
                : '<option value="">Search to load skills...</option>';
        }

        function render() {
            renderAgents();
            renderTeams();
            renderTasks();
            renderSkillResults();
            renderBenchOutput();
            renderDemoOutput();
        }

        function showPage(pageId) {
            const nextPage = document.getElementById(pageId) ? pageId : 'compose';
            document.querySelectorAll('.page').forEach(page => page.classList.toggle('active', page.id === nextPage));
            document.querySelectorAll('[data-page-link]').forEach(link => link.classList.toggle('active', link.dataset.pageLink === nextPage));
            if (window.location.hash !== `#${nextPage}`) {
                history.replaceState(null, '', `#${nextPage}`);
            }
            if (nextPage === 'skills') {
                loadSkillsList();
            }
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

        async function refreshBenchRuns() {
            const result = await api('/api/bench/runs');
            state.benchRuns = result.runs || [];
            renderBenchOutput();
        }

        async function loadBenchRun(runId) {
            const result = await api(`/api/bench/runs/${encodeURIComponent(runId)}`);
            state.selectedBenchRun = result;
            renderBenchOutput();
            return result;
        }

        async function loadConversation(taskId) {
            const result = await api(`/api/tasks/${encodeURIComponent(taskId)}/conversation`);
            const task = state.tasks.find(item => String(item.id) === String(taskId)) || {};
            state.selectedOutput = {
                task_id: result.task_id,
                status: result.result?.status || task.status || 'loaded',
                input: result.result?.input || task.input || '',
                conversation: result.conversation || [],
                result: result.result || null,
            };
            renderDemoOutput();
            return state.selectedOutput;
        }

        let autoPollBusy = false;
        async function autoPollTasks() {
            if (autoPollBusy) {
                return;
            }

            const activeTasks = state.tasks.filter(task => !['completed', 'failed'].includes(String(task.status)));
            if (activeTasks.length === 0) {
                return;
            }

            autoPollBusy = true;
            try {
                for (const task of activeTasks.slice(0, 6)) {
                    const status = await api(`/api/tasks/${encodeURIComponent(task.id)}/status`);
                    if (status.status === 'completed' || status.status === 'failed') {
                        await refresh();
                        await loadConversation(task.id);
                        showToast(`${task.id}: ${status.status}`);
                        break;
                    }
                }
                await refresh();
            } catch (error) {
                showToast(error.message, true);
            } finally {
                autoPollBusy = false;
            }
        }

        function formObject(form) {
            const formData = new FormData(form);
            const body = {};
            formData.forEach((value, key) => {
                if (key === 'skill_ids') {
                    if (!body[key]) body[key] = [];
                    body[key].push(value);
                } else {
                    body[key] = value;
                }
            });
            return body;
        }

        // Load skills into the skills picker
        async function loadSkillsPicker() {
            const picker = $('#skills-picker');
            if (!picker) return;
            try {
                const result = await api('/api/skills');
                const skills = result.skills || [];
                picker.innerHTML = skills.map(skill => `<option value="${skill.id}">${escapeHtml(skill.name)} - ${escapeHtml(skill.description || '')}</option>`).join('');
            } catch (error) {
                picker.innerHTML = '<option value="" disabled>Error loading skills</option>';
            }
        }

        loadSkillsPicker();

        // Skills page functionality
        async function loadSkillsList() {
            const container = $('#skills-list');
            if (!container) return;
            try {
                const result = await api('/api/skills');
                const skills = result.skills || [];
                container.innerHTML = skills.length === 0
                    ? '<p class="meta">No skills yet. Create one or import from skills.sh.</p>'
                    : skills.map(skill => `
                        <div style="padding: var(--spacing-md); border: 1px solid var(--hairline); border-radius: var(--radius-md); margin-bottom: var(--spacing-sm); background: var(--surface);">
                            <div style="display: flex; justify-content: space-between; align-items: start; gap: var(--spacing-md);">
                                <div style="flex: 1; min-width: 0;">
                                    <h3 style="margin: 0 0 var(--spacing-xs); font-size: 16px;">${escapeHtml(skill.name)}</h3>
                                    <p class="meta" style="margin: 0 0 var(--spacing-sm);">${escapeHtml(skill.description || '')}</p>
                                    <div class="meta" style="margin-top: var(--spacing-sm);">slug: <code>${escapeHtml(skill.slug)}</code> | source: ${escapeHtml(skill.source || 'manual')}</div>
                                </div>
                                <button class="btn secondary" type="button" data-skill-delete="${skill.id}">Delete</button>
                            </div>
                        </div>
                    `).join('');

                // Attach delete handlers
                container.querySelectorAll('[data-skill-delete]').forEach(btn => {
                    btn.addEventListener('click', async () => {
                        const skillId = btn.dataset.skillDelete;
                        if (!confirm('Delete this skill?')) return;
                        try {
                            await api(`/api/skills/${skillId}`, { method: 'DELETE' });
                            showToast('Skill deleted.');
                            loadSkillsList();
                            loadSkillsPicker();
                        } catch (error) {
                            showToast(error.message, true);
                        }
                    });
                });
            } catch (error) {
                container.innerHTML = `<p class="meta" style="color: var(--semantic-error);">Error loading skills: ${escapeHtml(error.message)}</p>`;
            }
        }

        $('#skill-form').addEventListener('submit', async (event) => {
            event.preventDefault();
            const body = formObject(event.currentTarget);
            try {
                await api('/api/skills', { method: 'POST', body: JSON.stringify(body) });
                showToast('Skill created.');
                event.currentTarget.reset();
                loadSkillsList();
                loadSkillsPicker();
            } catch (error) {
                showToast(error.message, true);
            }
        });

        // Skills.sh import
        let skillsShTimer = null;
        $('#skills-sh-search')?.addEventListener('input', (event) => {
            const query = event.target.value.trim();
            window.clearTimeout(skillsShTimer);
            const resultsContainer = $('#skills-sh-results');
            if (!query) {
                resultsContainer.innerHTML = '';
                return;
            }
            skillsShTimer = window.setTimeout(async () => {
                if (query.length < 2) {
                    resultsContainer.innerHTML = '';
                    return;
                }
                try {
                    const result = await api(`/api/skills/search?q=${encodeURIComponent(query)}&limit=8`);
                    const skills = result.skills || [];
                    resultsContainer.innerHTML = skills.length === 0
                        ? '<p class="meta">No results found.</p>'
                        : skills.map(skill => `
                            <div style="padding: var(--spacing-sm); border: 1px solid var(--hairline); border-radius: var(--radius-sm); margin-bottom: var(--spacing-xs); background: var(--surface);">
                                <div style="font-weight: 500; font-size: 14px;">${escapeHtml(skill.title || skill.id)}</div>
                                <div class="meta">${escapeHtml(skill.description || '')}</div>
                                <button class="btn secondary" type="button" style="margin-top: var(--spacing-xs); font-size: 12px;" data-skill-import="${skill.id}">Import</button>
                            </div>
                        `).join('');

                    resultsContainer.querySelectorAll('[data-skill-import]').forEach(btn => {
                        btn.addEventListener('click', async () => {
                            const skillId = btn.dataset.skillImport;
                            try {
                                await api('/api/skills/import/skills-sh', {
                                    method: 'POST',
                                    body: JSON.stringify({ id: skillId }),
                                });
                                showToast('Skill imported.');
                                loadSkillsList();
                                loadSkillsPicker();
                            } catch (error) {
                                showToast(error.message, true);
                            }
                        });
                    });
                } catch (error) {
                    resultsContainer.innerHTML = `<p class="meta" style="color: var(--semantic-error);">${escapeHtml(error.message)}</p>`;
                }
            }, 350);
        });

        // Load skills list when skills page is shown
        const observer = new MutationObserver(() => {
            if ($('#skills').classList.contains('active')) {
                loadSkillsList();
            }
        });
        observer.observe(document.body, { attributes: true, attributeFilter: ['class'] });

        $('#agent-form').addEventListener('submit', async (event) => {
            event.preventDefault();
            const body = formObject(event.currentTarget);
            if (body.llm_model === 'custom' && body.llm_model_custom) {
                body.llm_model = body.llm_model_custom;
            }
            try {
                await api('/api/agents', { method: 'POST', body: JSON.stringify(body) });
                showToast('Agent created.');
                await refresh();
            } catch (error) {
                showToast(error.message, true);
            }
        });

        $('#model-select').addEventListener('change', (event) => {
            const customField = $('#custom-model-field');
            if (event.target.value === 'custom') {
                customField.style.display = 'grid';
            } else {
                customField.style.display = 'none';
            }
        });

        $('#team-form').addEventListener('submit', async (event) => {
            event.preventDefault();
            const body = formObject(event.currentTarget);
            const nodes = orderedBuilderNodes();
            body.agent_order = nodes.map(node => node.role);
            body.nodes = nodes.map(node => ({
                id: node.id,
                role: node.role,
                x: Math.round(node.x),
                y: Math.round(node.y),
            }));
            body.edges = nodes.slice(1).map((node, index) => ({
                from: nodes[index].id,
                to: node.id,
            }));
            try {
                await api('/api/teams', { method: 'POST', body: JSON.stringify(body) });
                showToast('Team created.');
                await refresh();
            } catch (error) {
                showToast(error.message, true);
            }
        });

        $('#agent-order-input').addEventListener('input', () => {
            state.teamBuilderNodes = [];
            renderAgentOrderBuilder();
        });

        $('#agent-palette').addEventListener('dragstart', (event) => {
            const role = event.target.dataset.role;
            if (role) {
                event.dataTransfer.setData('text/plain', role);
                event.dataTransfer.effectAllowed = 'copy';
            }
        });

        $('#agent-node-canvas').addEventListener('dragstart', (event) => {
            const node = event.target.closest('.node');
            if (node) {
                const rect = node.getBoundingClientRect();
                event.dataTransfer.setData('text/plain', JSON.stringify({
                    nodeId: node.dataset.nodeId,
                    offsetX: event.clientX - rect.left,
                    offsetY: event.clientY - rect.top,
                }));
                event.dataTransfer.effectAllowed = 'move';
            }
        });

        $('#agent-node-canvas').addEventListener('dragover', (event) => {
            event.preventDefault();
            $('#agent-node-canvas').classList.add('drag-over');
        });

        $('#agent-node-canvas').addEventListener('dragleave', () => $('#agent-node-canvas').classList.remove('drag-over'));

        $('#agent-node-canvas').addEventListener('drop', (event) => {
            event.preventDefault();
            const canvas = $('#agent-node-canvas');
            canvas.classList.remove('drag-over');
            const raw = event.dataTransfer.getData('text/plain');
            if (!raw) {
                return;
            }

            const rect = canvas.getBoundingClientRect();
            let payload = { role: raw, offsetX: 105, offsetY: 42 };
            try {
                payload = JSON.parse(raw);
            } catch (_) {
            }

            const x = Math.max(16, Math.min(canvas.clientWidth - 226, event.clientX - rect.left - Number(payload.offsetX || 105)));
            const y = Math.max(16, Math.min(canvas.clientHeight - 100, event.clientY - rect.top - Number(payload.offsetY || 42)));
            if (payload.nodeId) {
                state.teamBuilderNodes = state.teamBuilderNodes.map(node => (
                    node.id === payload.nodeId ? { ...node, x, y } : node
                ));
            } else if (payload.role) {
                state.teamBuilderNodes.push({
                    id: `builder-${Date.now()}-${state.teamBuilderNodes.length}`,
                    role: payload.role,
                    x,
                    y,
                });
            }
            renderAgentOrderBuilder();
        });

        $('#agent-node-canvas').addEventListener('click', (event) => {
            const nodeId = event.target.dataset.removeNode;
            if (!nodeId) {
                return;
            }
            state.teamBuilderNodes = state.teamBuilderNodes.filter(node => node.id !== nodeId);
            renderAgentOrderBuilder();
        });

        $('#bench-form').addEventListener('submit', async (event) => {
            event.preventDefault();
            const body = formObject(event.currentTarget);
            body.agent_id = Number(body.agent_id);
            body.timeout_sec = Number(body.timeout_sec || 3600);
            body.extra_args = [];
            try {
                const run = await api('/api/bench/runs', { method: 'POST', body: JSON.stringify(body) });
                state.selectedBenchRun = run;
                showToast(`Terminal-Bench run started: ${run.id}`);
                await refreshBenchRuns();
            } catch (error) {
                showToast(error.message, true);
            }
        });

        $('#refresh-bench').addEventListener('click', () => refreshBenchRuns().catch(error => showToast(error.message, true)));

        $('#bench-output').addEventListener('click', async (event) => {
            const runId = event.target.dataset.benchRun;
            if (!runId) {
                return;
            }
            try {
                await loadBenchRun(runId);
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
                await refresh();
                state.selectedOutput = {
                    task_id: task.task_id,
                    status: task.status,
                    input: body.input,
                    conversation: [],
                    result: null,
                };
                renderDemoOutput();
                showToast(`Task queued: ${task.task_id}`);
            } catch (error) {
                showToast(error.message, true);
            }
        });

        $('#refresh').addEventListener('click', () => refresh().catch(error => showToast(error.message, true)));

        $('#agents-list').addEventListener('click', async (event) => {
            const agentId = event.target.dataset.deleteAgent;
            if (!agentId || !window.confirm('Delete this agent record?')) {
                return;
            }

            try {
                await api(`/api/agents/${encodeURIComponent(agentId)}`, { method: 'DELETE' });
                showToast('Agent deleted.');
                await refresh();
            } catch (error) {
                showToast(error.message, true);
            }
        });

        $('#teams-list').addEventListener('click', async (event) => {
            const teamId = event.target.dataset.deleteTeam;
            if (!teamId || !window.confirm('Delete this team?')) {
                return;
            }

            try {
                await api(`/api/teams/${encodeURIComponent(teamId)}`, { method: 'DELETE' });
                showToast('Team deleted.');
                await refresh();
            } catch (error) {
                showToast(error.message, true);
            }
        });

        $('#tasks-list').addEventListener('click', async (event) => {
            const statusId = event.target.dataset.status;
            const convoId = event.target.dataset.convo;
            const deleteId = event.target.dataset.deleteTask;
            try {
                if (statusId) {
                    const result = await api(`/api/tasks/${encodeURIComponent(statusId)}/status`);
                    const task = state.tasks.find(item => String(item.id) === String(statusId)) || {};
                    state.selectedOutput = {
                        ...(state.selectedOutput || {}),
                        task_id: result.task_id,
                        status: result.status,
                        input: task.input || state.selectedOutput?.input || '',
                        conversation: state.selectedOutput?.conversation || task.conversation || [],
                        result: state.selectedOutput?.result || task.result || null,
                    };
                    renderDemoOutput();
                    showToast(`${result.task_id}: ${result.status}`);
                    await refresh();
                    if (result.status === 'completed' || result.status === 'failed') {
                        await loadConversation(statusId);
                    }
                }
                if (convoId) {
                    const output = await loadConversation(convoId);
                    const turns = Array.isArray(output.conversation) ? output.conversation.length : 0;
                    showToast(`${output.task_id}: ${turns} conversation turns`);
                }
                if (deleteId && window.confirm('Delete this task record?')) {
                    await api(`/api/tasks/${encodeURIComponent(deleteId)}`, { method: 'DELETE' });
                    if (state.selectedOutput?.task_id === deleteId) {
                        state.selectedOutput = null;
                    }
                    showToast('Task deleted.');
                    await refresh();
                }
            } catch (error) {
                showToast(error.message, true);
            }
        });

        document.querySelectorAll('[data-page-link]').forEach(link => {
            link.addEventListener('click', (event) => {
                event.preventDefault();
                showPage(link.dataset.pageLink);
            });
        });

        window.addEventListener('hashchange', () => showPage(window.location.hash.slice(1) || 'compose'));
        showPage(window.location.hash.slice(1) || 'compose');

        refresh().catch(error => {
            render();
            showToast(error.message, true);
        });
        window.setInterval(autoPollTasks, 5000);
        window.setInterval(() => {
            const activeRuns = state.benchRuns.filter(run => !['completed', 'failed'].includes(String(run.status)));
            if (activeRuns.length > 0) {
                refreshBenchRuns().catch(error => showToast(error.message, true));
                if (state.selectedBenchRun?.id) {
                    loadBenchRun(state.selectedBenchRun.id).catch(() => {});
                }
            }
        }, 5000);

        const promptData = [
            { category: "AI code editors", slug: "ai-code-editors", tools: [
                { name: "Cursor", detail: "Agent Prompt 2.0 + tool schemas", tags: ["prompt","tools"], prompt: "You are an AI coding assistant powered by advanced language models. Your goal is to help users write, edit, debug, and understand code. Provide clear explanations, follow best practices, and ask clarifying questions when needed." },
                { name: "Windsurf", detail: "Tool definitions through Wave 11", tags: ["prompt","tools"], prompt: "You are Windsurf, an AI coding assistant. You have access to tools for reading, writing, and editing files. Always think step by step before making changes. Explain your reasoning clearly." },
                { name: "VSCode Agent", detail: "GitHub Copilot agent mode", tags: ["prompt","tools"], prompt: "You are an AI programming assistant integrated into VS Code. Help users write high-quality code, debug issues, and understand complex codebases. Use available tools to search, read, and modify files." },
                { name: "Augment Code", detail: "Includes GPT-5 tools JSON config", tags: ["prompt","tools","model"], prompt: "You are Augment, an AI code assistant. Analyze the codebase thoroughly before making suggestions. Prioritize correctness, readability, and performance." },
                { name: "Trae", detail: "ByteDance AI code editor", tags: ["prompt"], prompt: "You are Trae, an AI coding assistant by ByteDance. Help users write efficient, maintainable code. Provide context-aware suggestions and explain your changes." },
                { name: "Traycer AI", detail: "Agentic code review tool", tags: ["prompt"], prompt: "You are Traycer, an AI code reviewer. Review code for bugs, security issues, performance problems, and style violations. Provide actionable feedback with specific line references." },
                { name: "Qoder", detail: "AI coding assistant", tags: ["prompt"], prompt: "You are Qoder, an AI coding assistant. Help users solve programming challenges with clear, well-documented solutions." },
                { name: "Warp.dev", detail: "AI terminal + agent prompt", tags: ["prompt","tools"], prompt: "You are Warp, an AI terminal assistant. Help users with command-line tasks, shell scripting, and system administration. Suggest safe, idempotent commands." },
                { name: "Xcode", detail: "Apple Swift/Xcode AI assist", tags: ["prompt"], prompt: "You are an AI assistant for Xcode. Help with Swift, SwiftUI, and Apple platform development. Follow Apple's Human Interface Guidelines and best practices." },
                { name: "Z.ai Code", detail: "Z.ai coding assistant", tags: ["prompt"], prompt: "You are Z.ai Code, an AI coding assistant. Write clean, efficient code and provide thorough explanations." },
                { name: "Amp", detail: "Sonnet + GPT-5 prompt configs", tags: ["prompt","model"], prompt: "You are Amp, an AI code assistant. Leverage deep codebase understanding to provide accurate, context-aware suggestions." },
                { name: "Kiro", detail: "AWS AI code agent", tags: ["prompt","tools"], prompt: "You are Kiro, an AWS AI code agent. Help users build, deploy, and manage cloud applications following AWS best practices." },
            ]},
            { category: "Autonomous agents", slug: "autonomous-agents", tools: [
                { name: "Devin AI", detail: "Full agent + DeepWiki sub-prompt", tags: ["prompt","tools"], prompt: "You are Devin, an autonomous AI software engineer. You can plan, code, debug, and deploy complete software projects. Break down complex tasks, work iteratively, and communicate progress clearly." },
                { name: "Manus", detail: "Agent tools & full system prompt", tags: ["prompt","tools","config"], prompt: "You are Manus, an autonomous AI agent. Execute multi-step tasks independently using available tools. Plan before acting, handle errors gracefully, and report results clearly." },
                { name: "Junie", detail: "JetBrains autonomous agent", tags: ["prompt","tools"], prompt: "You are Junie, JetBrains autonomous coding agent. Understand project structure, follow IDE conventions, and produce production-ready code." },
                { name: "Comet Assistant", detail: "Perplexity-backed agent", tags: ["prompt"], prompt: "You are Comet, an AI research assistant powered by Perplexity. Search the web, synthesize information, and provide well-cited answers." },
                { name: "Emergent", detail: "Agentic workflow orchestrator", tags: ["prompt","tools"], prompt: "You are Emergent, an AI workflow orchestrator. Coordinate multi-step processes, delegate subtasks, and ensure end-to-end completion." },
                { name: "Poke", detail: "Browser automation agent", tags: ["prompt"], prompt: "You are Poke, a browser automation agent. Navigate websites, extract data, fill forms, and automate web interactions reliably." },
            ]},
            { category: "App builders", slug: "app-builders", tools: [
                { name: "v0", detail: "Vercel UI generator", tags: ["prompt","tools"], prompt: "You are v0, Vercel's AI UI generator. Create beautiful, responsive React components using Tailwind CSS and shadcn/ui. Prioritize accessibility and modern design patterns." },
                { name: "Lovable", detail: "Full-stack app from natural language", tags: ["prompt"], prompt: "You are Lovable, an AI full-stack app builder. Generate complete applications from natural language descriptions. Use modern frameworks and best practices." },
                { name: "Replit", detail: "Replit Agent system prompt", tags: ["prompt","tools"], prompt: "You are the Replit Agent. Help users build, deploy, and share applications directly from their browser. Make development accessible and fun." },
                { name: "Same.dev", detail: "UI clone / prompt manager", tags: ["prompt"], prompt: "You are Same.dev, an AI UI cloning tool. Recreate user interfaces from screenshots or descriptions with pixel-perfect accuracy." },
                { name: "Leap.new", detail: "App scaffolding agent", tags: ["prompt"], prompt: "You are Leap.new, an AI app scaffolding agent. Generate project boilerplate with best-practice configurations for rapid development." },
                { name: "Orchids.app", detail: "AI web design tool", tags: ["prompt"], prompt: "You are Orchids, an AI web design assistant. Create stunning, user-friendly web designs with modern aesthetics and best UX practices." },
            ]},
            { category: "AI assistants & productivity", slug: "ai-assistants", tools: [
                { name: "Perplexity", detail: "Search-augmented assistant", tags: ["prompt","model"], prompt: "You are Perplexity, an AI-powered search assistant. Provide accurate, up-to-date answers with citations from reliable sources. Be thorough and objective." },
                { name: "NotionAI", detail: "Notion writing + Q&A assistant", tags: ["prompt"], prompt: "You are Notion AI, a writing and productivity assistant integrated into Notion. Help users write, edit, brainstorm, summarize, and organize their work effectively." },
                { name: "Cluely", detail: "Meeting/interview copilot", tags: ["prompt"], prompt: "You are Cluely, an AI meeting and interview copilot. Help users prepare, take notes, and follow up on conversations effectively." },
                { name: "Dia", detail: "The Browser Company AI browser", tags: ["prompt"], prompt: "You are Dia, The Browser Company's AI browser assistant. Help users navigate the web, organize information, and automate browser tasks." },
                { name: "CodeBuddy", detail: "Tencent coding assistant", tags: ["prompt"], prompt: "You are CodeBuddy, Tencent's AI coding assistant. Help developers write, review, and optimize code with context-aware suggestions." },
            ]},
            { category: "Model providers", slug: "model-providers", tools: [
                { name: "Anthropic (Claude Code)", detail: "Claude Code full system prompt", tags: ["prompt","config"], prompt: "You are Claude, an AI assistant created by Anthropic. You are helpful, honest, and harmless. You think carefully before responding, admit uncertainty, and avoid hallucination. For coding tasks, write clean, well-documented code and explain your reasoning." },
                { name: "Google", detail: "Gemini assistant prompt configs", tags: ["prompt","model"], prompt: "You are Gemini, Google's AI assistant. Provide accurate, helpful responses across a wide range of topics. Be thorough, creative, and grounded in facts." },
            ]},
            { category: "Open source prompts", slug: "open-source", tools: [
                { name: "Open Source prompts", detail: "Community-contributed prompt collection", tags: ["prompt"], prompt: "You are an AI assistant configured with community-vetted system prompts. Follow instructions carefully, be helpful and accurate, and prioritize user needs." },
            ]}
        ];

        function promptOptions() {
            return promptData.flatMap(category => category.tools.map(tool => ({
                category: category.category,
                name: tool.name,
                label: `${category.category} / ${tool.name}`,
                prompt: tool.prompt,
            })));
        }

        function renderPromptSelect() {
            const select = document.getElementById('prompt-select');
            if (!select) {
                return;
            }

            select.innerHTML = '<option value="">Select a popular system prompt...</option>' + promptOptions().map((item, index) => (
                `<option value="${index}">${escapeHtml(item.label)}</option>`
            )).join('');
        }

        document.getElementById('prompt-select').addEventListener('change', (event) => {
            const value = event.target.value;
            if (value === '') {
                return;
            }

            const item = promptOptions()[Number(value)];
            if (!item) {
                return;
            }

            const roleSelect = document.querySelector('#agent-form select[name="role"]');
            const currentRole = roleSelect ? roleSelect.value : 'researcher';
            const nameInput = document.querySelector('#agent-form input[name="name"]');
            if (nameInput && !nameInput.value.trim()) {
                nameInput.value = `${item.name} ${currentRole}`;
            }
            const promptTextarea = document.querySelector('#agent-form textarea[name="system_prompt"]');
            if (promptTextarea) {
                promptTextarea.value = `[${item.name}] ${item.prompt}`;
            }
            showToast(`Loaded ${item.name} system prompt for ${currentRole} role.`);
        });

        renderPromptSelect();
    </script>
</body>
</html>
HTML;
}

function dashboard_fallback_skills(string $query): array
{
    $skills = [
        ['id' => 'react-best-practices', 'title' => 'React Best Practices', 'description' => 'Build performant React and Next.js applications.', 'source' => 'skills.sh/fallback', 'content' => "# React Best Practices\n\nUse this skill when writing, reviewing, or refactoring React and Next.js code. Prioritize eliminating waterfalls, reducing bundle size, server-side performance, stable rendering, accessibility, and clean component boundaries. Return concrete findings and recommended code-level changes."],
        ['id' => 'security-reviewer', 'title' => 'Security Reviewer', 'description' => 'Review software for security risks and unsafe implementation patterns.', 'source' => 'skills.sh/fallback', 'content' => "# Security Reviewer\n\nReview the task for authentication, authorization, injection, secret handling, input validation, dependency, transport, and data exposure risks. Prioritize exploitable issues, explain impact, and provide remediation steps."],
        ['id' => 'performance-engineer', 'title' => 'Performance Engineer', 'description' => 'Find performance bottlenecks and propose optimizations.', 'source' => 'skills.sh/fallback', 'content' => "# Performance Engineer\n\nAnalyze latency, throughput, CPU, memory, database, network, caching, and rendering concerns. Identify likely bottlenecks, prioritize by impact, and recommend measurable optimizations."],
        ['id' => 'technical-writer', 'title' => 'Technical Writer', 'description' => 'Create clear developer-facing documentation.', 'source' => 'skills.sh/fallback', 'content' => "# Technical Writer\n\nWrite clear, accurate, structured technical documentation. Include prerequisites, step-by-step usage, examples, troubleshooting, and concise summaries for developers."],
        ['id' => 'api-designer', 'title' => 'API Designer', 'description' => 'Design and review clean HTTP/JSON APIs.', 'source' => 'skills.sh/fallback', 'content' => "# API Designer\n\nDesign APIs with clear resources, predictable methods, consistent status codes, validation, pagination, idempotency, versioning, and error formats. Provide example requests and responses."],
        ['id' => 'devops-engineer', 'title' => 'DevOps Engineer', 'description' => 'Review infrastructure, deployment, and operations tasks.', 'source' => 'skills.sh/fallback', 'content' => "# DevOps Engineer\n\nFocus on Docker, CI/CD, Kubernetes, observability, scaling, resilience, configuration, secrets, and deployment safety. Provide actionable operational steps and checks."],
    ];

    $needle = strtolower($query);
    return array_values(array_filter($skills, static function (array $skill) use ($needle): bool {
        return str_contains(strtolower($skill['id'] . ' ' . $skill['title'] . ' ' . $skill['description']), $needle);
    }));
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
            'PUT /api/agents/{id}',
            'POST /api/teams',
            'POST /api/tasks',
            'GET /api/tasks/{id}/status',
            'GET /api/tasks/{id}/conversation',
            'POST /api/webhooks/n8n',
            'GET /api/skills',
            'POST /api/skills',
            'GET /api/skills/{slug}',
            'PUT /api/skills/{slug}',
            'DELETE /api/skills/{slug}',
            'POST /api/skills/import/skills-sh',
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

if ($method === 'GET' && $path === '/api/bench/runs') {
    $response = @file_get_contents('http://harbor-runner:8090/runs');
    $data = is_string($response) ? json_decode($response, true) : null;
    respond(is_array($data) ? $data : ['runs' => [], 'error' => 'Harbor runner unavailable']);
    return;
}

if ($method === 'GET' && preg_match('#^/api/bench/runs/([^/]+)$#', $path, $matches) === 1) {
    $runId = rawurlencode((string)$matches[1]);
    $response = @file_get_contents('http://harbor-runner:8090/runs/' . $runId);
    $data = is_string($response) ? json_decode($response, true) : null;
    if (!is_array($data)) {
        respond(['error' => 'Harbor runner unavailable'], 502);
        return;
    }
    respond($data);
    return;
}

if ($method === 'POST' && $path === '/api/bench/runs') {
    $body = json_body();
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\nAccept: application/json\r\n",
            'content' => json_encode($body, JSON_UNESCAPED_SLASHES),
            'ignore_errors' => true,
            'timeout' => 10,
        ],
    ]);
    $response = @file_get_contents('http://harbor-runner:8090/runs', false, $context);
    $data = is_string($response) ? json_decode($response, true) : null;
    if (!is_array($data)) {
        respond(['error' => 'Harbor runner unavailable'], 502);
        return;
    }
    respond($data, 202);
    return;
}

function skills_slugify(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? $value;
    $value = trim($value, '-');
    return $value === '' ? 'skill-' . substr(bin2hex(random_bytes(3)), 0, 6) : $value;
}

function skills_parse_frontmatter(string $markdown): array
{
    $name = '';
    $description = '';
    $body = $markdown;
    if (str_starts_with($markdown, '---')) {
        $end = strpos($markdown, "\n---", 3);
        if ($end !== false) {
            $front = substr($markdown, 3, $end - 3);
            $body = ltrim(substr($markdown, $end + 4), "\r\n");
            foreach (preg_split('/\r?\n/', trim($front)) as $line) {
                if (preg_match('/^([a-zA-Z_]+)\s*:\s*(.*)$/', $line, $m) === 1) {
                    $key = strtolower($m[1]);
                    $val = trim($m[2], " \"'");
                    if ($key === 'name') {
                        $name = $val;
                    } elseif ($key === 'description') {
                        $description = $val;
                    }
                }
            }
        }
    }
    if ($name === '' && preg_match('/^#\s+(.+)$/m', $body, $m) === 1) {
        $name = trim($m[1]);
    }
    if ($description === '') {
        $firstParagraph = trim(preg_split('/\r?\n\r?\n/', $body)[0] ?? '');
        $description = mb_substr(preg_replace('/\s+/', ' ', $firstParagraph) ?? '', 0, 220);
    }
    return ['name' => $name, 'description' => $description, 'body' => $body];
}

function skills_index_for_agent(array $agent): array
{
    $skillIds = $agent['skill_ids'] ?? [];
    if (!is_array($skillIds) || $skillIds === []) {
        return [];
    }

    $rows = store('skills')->all();
    $bySlug = [];
    foreach ($rows as $row) {
        $bySlug[(string)($row['slug'] ?? $row['id'] ?? '')] = $row;
    }

    $index = [];
    foreach ($skillIds as $slug) {
        $skill = $bySlug[(string)$slug] ?? null;
        if ($skill === null) {
            continue;
        }
        $index[] = [
            'slug' => (string)($skill['slug'] ?? $skill['id'] ?? $slug),
            'name' => (string)($skill['name'] ?? ''),
            'description' => (string)($skill['description'] ?? ''),
        ];
    }
    return $index;
}

if ($method === 'GET' && $path === '/api/skills') {
    $rows = store('skills')->all();
    $skills = array_map(static function (array $row): array {
        return [
            'id' => $row['id'] ?? $row['slug'] ?? '',
            'slug' => $row['slug'] ?? $row['id'] ?? '',
            'name' => $row['name'] ?? '',
            'description' => $row['description'] ?? '',
            'source' => $row['source'] ?? 'manual',
            'updated_at' => $row['updated_at'] ?? $row['created_at'] ?? null,
        ];
    }, $rows);
    respond(['skills' => $skills]);
    return;
}

if ($method === 'POST' && $path === '/api/skills') {
    $body = json_body();
    $name = trim((string)($body['name'] ?? ''));
    $rawBody = (string)($body['body'] ?? '');
    $description = trim((string)($body['description'] ?? ''));

    if ($name === '' && $rawBody === '') {
        respond(['error' => 'name or body is required'], 422);
        return;
    }

    if ($rawBody !== '') {
        $parsed = skills_parse_frontmatter($rawBody);
        if ($name === '') {
            $name = $parsed['name'];
        }
        if ($description === '') {
            $description = $parsed['description'];
        }
        $rawBody = $parsed['body'];
    }

    if ($name === '') {
        respond(['error' => 'name is required'], 422);
        return;
    }

    $slug = trim((string)($body['slug'] ?? ''));
    if ($slug === '') {
        $slug = skills_slugify($name);
    } else {
        $slug = skills_slugify($slug);
    }

    $existing = store('skills')->firstWhere(static fn (array $row): bool => (string)($row['slug'] ?? $row['id'] ?? '') === $slug);
    if ($existing !== null) {
        respond(['error' => 'slug already exists', 'slug' => $slug], 409);
        return;
    }

    $record = [
        'id' => $slug,
        'slug' => $slug,
        'name' => $name,
        'description' => $description,
        'body' => $rawBody,
        'source' => trim((string)($body['source'] ?? 'manual')) ?: 'manual',
        'source_ref' => trim((string)($body['source_ref'] ?? '')) ?: null,
        'created_at' => date(DATE_ATOM),
        'updated_at' => date(DATE_ATOM),
    ];
    store('skills')->append($record);
    respond($record, 201);
    return;
}

if ($method === 'POST' && $path === '/api/skills/import/skills-sh') {
    $body = json_body();
    $skillId = trim((string)($body['id'] ?? ''));
    if ($skillId === '') {
        respond(['error' => 'id is required'], 422);
        return;
    }

    $url = 'https://api.skyll.app/skills/' . rawurlencode($skillId);
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "Accept: application/json\r\n",
            'ignore_errors' => true,
            'timeout' => 8,
        ],
    ]);
    $response = @file_get_contents($url, false, $context);
    $data = is_string($response) ? json_decode($response, true) : null;

    if (!is_array($data)) {
        $fallback = dashboard_fallback_skills($skillId);
        $data = $fallback[0] ?? null;
    }
    if (!is_array($data)) {
        respond(['error' => 'skill not found upstream'], 502);
        return;
    }

    $name = trim((string)($data['title'] ?? $data['name'] ?? $skillId));
    $description = trim((string)($data['description'] ?? ''));
    $content = (string)($data['content'] ?? $data['body'] ?? '');
    $slug = skills_slugify($skillId !== '' ? $skillId : $name);

    $existing = store('skills')->firstWhere(static fn (array $row): bool => (string)($row['slug'] ?? $row['id'] ?? '') === $slug);
    if ($existing !== null) {
        respond(['skill' => $existing, 'duplicate' => true]);
        return;
    }

    $record = [
        'id' => $slug,
        'slug' => $slug,
        'name' => $name,
        'description' => $description,
        'body' => $content,
        'source' => 'skills.sh',
        'source_ref' => $skillId,
        'created_at' => date(DATE_ATOM),
        'updated_at' => date(DATE_ATOM),
    ];
    store('skills')->append($record);
    respond($record, 201);
    return;
}

if ($method === 'GET' && $path === '/api/skills/search') {
    $query = trim((string)($_GET['q'] ?? ''));
    $limit = max(1, min(20, (int)($_GET['limit'] ?? 8)));
    if (strlen($query) < 2) {
        respond(['skills' => []]);
        return;
    }

    $url = 'https://api.skyll.app/search?q=' . rawurlencode($query) . '&limit=' . $limit;
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "Accept: application/json\r\n",
            'ignore_errors' => true,
            'timeout' => 8,
        ],
    ]);
    $response = @file_get_contents($url, false, $context);
    $data = is_string($response) ? json_decode($response, true) : null;
    if (!is_array($data)) {
        respond(['skills' => dashboard_fallback_skills($query), 'source' => 'fallback']);
        return;
    }

    $skills = array_map(static fn (array $skill): array => [
            'id' => (string)($skill['id'] ?? $skill['title'] ?? ''),
            'title' => (string)($skill['title'] ?? $skill['id'] ?? ''),
            'description' => (string)($skill['description'] ?? ''),
            'source' => (string)($skill['source'] ?? ''),
            'install_count' => (int)($skill['install_count'] ?? 0),
            'content' => (string)($skill['content'] ?? ''),
            'raw_url' => (string)($skill['refs']['raw'] ?? ''),
            'skills_url' => (string)($skill['refs']['skills_sh'] ?? ''),
        ], array_values($data['skills'] ?? []));

    respond([
        'skills' => $skills,
        'source' => 'skills.sh',
    ]);
    return;
}

if ($method === 'GET' && preg_match('#^/api/skills/([^/]+)$#', $path, $matches) === 1) {
    $slug = (string)$matches[1];
    $skill = store('skills')->firstWhere(static fn (array $row): bool => (string)($row['slug'] ?? $row['id'] ?? '') === $slug);
    if ($skill === null) {
        respond(['error' => 'skill not found'], 404);
        return;
    }
    respond($skill);
    return;
}

if ($method === 'PUT' && preg_match('#^/api/skills/([^/]+)$#', $path, $matches) === 1) {
    $slug = (string)$matches[1];
    $body = json_body();
    $updated = store('skills')->updateWhere(
        static fn (array $row): bool => (string)($row['slug'] ?? $row['id'] ?? '') === $slug,
        static function (array $row) use ($body): array {
            foreach (['name', 'description', 'body', 'source', 'source_ref'] as $key) {
                if (array_key_exists($key, $body)) {
                    $row[$key] = is_string($body[$key]) ? $body[$key] : $row[$key];
                }
            }
            $row['updated_at'] = date(DATE_ATOM);
            return $row;
        }
    );
    if ($updated === null) {
        respond(['error' => 'skill not found'], 404);
        return;
    }
    respond($updated);
    return;
}

if ($method === 'DELETE' && preg_match('#^/api/skills/([^/]+)$#', $path, $matches) === 1) {
    $slug = (string)$matches[1];
    $deleted = store('skills')->deleteWhere(static fn (array $row): bool => (string)($row['slug'] ?? $row['id'] ?? '') === $slug);
    if ($deleted === null) {
        respond(['error' => 'skill not found'], 404);
        return;
    }
    foreach (store('agents')->all() as $agent) {
        $skillIds = $agent['skill_ids'] ?? [];
        if (is_array($skillIds) && in_array($slug, $skillIds, true)) {
            store('agents')->updateWhere(
                static fn (array $row): bool => (string)$row['id'] === (string)$agent['id'],
                static function (array $row) use ($slug): array {
                    $row['skill_ids'] = array_values(array_filter(
                        $row['skill_ids'] ?? [],
                        static fn ($v): bool => (string)$v !== $slug
                    ));
                    $row['updated_at'] = date(DATE_ATOM);
                    return $row;
                }
            );
        }
    }
    respond(['deleted' => $deleted]);
    return;
}

if ($method === 'DELETE' && preg_match('#^/api/agents/([^/]+)$#', $path, $matches) === 1) {
    $agentId = (string)$matches[1];
    $deleted = store('agents')->deleteWhere(static fn (array $row): bool => (string)$row['id'] === $agentId);
    if ($deleted === null) {
        respond(['error' => 'agent not found'], 404);
        return;
    }

    respond(['deleted' => $deleted]);
    return;
}

if ($method === 'DELETE' && preg_match('#^/api/teams/([^/]+)$#', $path, $matches) === 1) {
    $teamId = (string)$matches[1];
    $deleted = store('teams')->deleteWhere(static fn (array $row): bool => (string)$row['id'] === $teamId);
    if ($deleted === null) {
        respond(['error' => 'team not found'], 404);
        return;
    }

    respond(['deleted' => $deleted]);
    return;
}

if ($method === 'DELETE' && preg_match('#^/api/tasks/([^/]+)$#', $path, $matches) === 1) {
    $taskId = (string)$matches[1];
    $deleted = store('tasks')->deleteWhere(static fn (array $row): bool => (string)$row['id'] === $taskId);
    if ($deleted === null) {
        respond(['error' => 'task not found'], 404);
        return;
    }

    respond(['deleted' => $deleted]);
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

    $skillIds = $body['skill_ids'] ?? [];
    if (!is_array($skillIds)) {
        $skillIds = [];
    }
    $skillIds = array_values(array_unique(array_filter(array_map(
        static fn ($v): string => trim((string)$v),
        $skillIds
    ))));

    $record = [
        'id' => store('agents')->nextId(),
        'name' => trim((string)$body['name']),
        'role' => trim((string)$body['role']),
        'llm_model' => trim((string)$body['llm_model']),
        'system_prompt' => trim((string)$body['system_prompt']),
        'temperature' => (float)($body['temperature'] ?? 0.2),
        'skill_ids' => $skillIds,
        'created_at' => date(DATE_ATOM),
    ];

    store('agents')->append($record);
    respond($record, 201);
    return;
}

if ($method === 'PUT' && preg_match('#^/api/agents/([^/]+)$#', $path, $matches) === 1) {
    $agentId = (string)$matches[1];
    $body = json_body();
    $updated = store('agents')->updateWhere(
        static fn (array $row): bool => (string)$row['id'] === $agentId,
        static function (array $row) use ($body): array {
            $writable = ['name', 'role', 'llm_model', 'system_prompt'];
            foreach ($writable as $key) {
                if (isset($body[$key]) && trim((string)$body[$key]) !== '') {
                    $row[$key] = trim((string)$body[$key]);
                }
            }
            if (isset($body['temperature'])) {
                $row['temperature'] = (float)$body['temperature'];
            }
            if (array_key_exists('skill_ids', $body)) {
                $skills = is_array($body['skill_ids']) ? $body['skill_ids'] : [];
                $row['skill_ids'] = array_values(array_unique(array_filter(array_map(
                    static fn ($v): string => trim((string)$v),
                    $skills
                ))));
            }
            $row['updated_at'] = date(DATE_ATOM);
            return $row;
        }
    );

    if ($updated === null) {
        respond(['error' => 'agent not found'], 404);
        return;
    }

    respond($updated);
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
    $agentRoles = array_map(
        static fn (array $agent): string => (string)($agent['role'] ?? ''),
        store('agents')->all()
    );
    $missingRoles = array_values(array_diff(
        array_values(array_map(static fn ($v): string => (string)$v, $agentOrder)),
        array_values(array_filter($agentRoles))
    ));
    if ($missingRoles !== []) {
        respond(['error' => 'team contains roles without agents: ' . implode(', ', $missingRoles)], 422);
        return;
    }
    $nodes = $body['nodes'] ?? [];
    $edges = $body['edges'] ?? [];
    $nodes = is_array($nodes) ? array_values($nodes) : [];
    $edges = is_array($edges) ? array_values($edges) : [];

    $record = [
        'id' => store('teams')->nextId(),
        'name' => trim((string)$body['name']),
        'agent_order' => array_values(array_map(static fn ($v): string => (string)$v, $agentOrder)),
        'nodes' => $nodes,
        'edges' => $edges,
        'created_at' => date(DATE_ATOM),
    ];

    store('teams')->append($record);
    respond($record, 201);
    return;
}

if ($method === 'POST' && preg_match('#^/api/agents/([^/]+)/tasks$#', $path, $matches) === 1) {
    $agentId = (string)$matches[1];
    $body = json_body();
    $input = trim((string)($body['input'] ?? ''));
    if ($input === '') {
        respond(['error' => 'input is required'], 422);
        return;
    }

    $agent = store('agents')->firstWhere(static fn (array $row): bool => (string)$row['id'] === $agentId);
    if ($agent === null) {
        respond(['error' => 'agent not found'], 404);
        return;
    }

    $taskId = uniqid('task_', true);
    $role = (string)$agent['role'];
    $maxRetries = max(0, (int)($body['max_retries'] ?? 1));
    $roleConfigs = [
        $role => [
            'agent_id' => $agent['id'] ?? null,
            'name' => $agent['name'] ?? $role,
            'llm_model' => $agent['llm_model'] ?? null,
            'system_prompt' => $agent['system_prompt'] ?? null,
            'temperature' => $agent['temperature'] ?? 0.2,
            'skill_ids' => $agent['skill_ids'] ?? [],
        ],
    ];
    $skillsIndex = [
        $role => skills_index_for_agent($agent),
    ];

    $task = [
        'id' => $taskId,
        'team_id' => null,
        'agent_id' => $agentId,
        'input' => $input,
        'status' => 'queued',
        'conversation' => [],
        'result' => null,
        'role_configs' => $roleConfigs,
        'created_at' => date(DATE_ATOM),
        'updated_at' => date(DATE_ATOM),
    ];
    store('tasks')->append($task);

    $coreRoles = ['researcher', 'summarizer', 'reviewer'];
    $queue = in_array($role, $coreRoles, true) ? sprintf('task-queue-%s', $role) : 'task-queue-skill';
    sqs()->sendMessage($queue, json_encode([
        'task_id' => $taskId,
        'agent_id' => $agentId,
        'input' => $input,
        'agent_order' => [$role],
        'max_retries' => $maxRetries,
        'target_role' => $role,
        'conversation' => [],
        'role_configs' => $roleConfigs,
        'skills_index' => $skillsIndex,
    ], JSON_UNESCAPED_SLASHES));

    respond([
        'task_id' => $taskId,
        'status' => 'queued',
        'agent_id' => $agentId,
    ], 202);
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

    $agents = store('agents')->all();
    $roleConfigs = [];
    $skillsIndex = [];
    foreach ($agentOrder as $role) {
        foreach ($agents as $agent) {
            if ((string)($agent['role'] ?? '') === (string)$role) {
                $roleConfigs[(string)$role] = [
                    'agent_id' => $agent['id'] ?? null,
                    'name' => $agent['name'] ?? (string)$role,
                    'llm_model' => $agent['llm_model'] ?? null,
                    'system_prompt' => $agent['system_prompt'] ?? null,
                    'temperature' => $agent['temperature'] ?? 0.2,
                    'skill_ids' => $agent['skill_ids'] ?? [],
                ];
                $skillsIndex[(string)$role] = skills_index_for_agent($agent);
                break;
            }
        }
    }
    $missingRoles = array_values(array_diff(
        array_values(array_map(static fn ($v): string => (string)$v, $agentOrder)),
        array_keys($roleConfigs)
    ));
    if ($missingRoles !== []) {
        respond(['error' => 'team contains roles without agents: ' . implode(', ', $missingRoles)], 422);
        return;
    }

    $task = [
        'id' => $taskId,
        'team_id' => $teamId,
        'input' => $input,
        'status' => 'queued',
        'conversation' => [],
        'result' => null,
        'role_configs' => $roleConfigs,
        'created_at' => date(DATE_ATOM),
        'updated_at' => date(DATE_ATOM),
    ];

    store('tasks')->append($task);

    $coreRoles = ['researcher', 'summarizer', 'reviewer'];
    $firstQueue = in_array($firstRole, $coreRoles, true) ? sprintf('task-queue-%s', $firstRole) : 'task-queue-skill';

    sqs()->sendMessage($firstQueue, json_encode([
        'task_id' => $taskId,
        'team_id' => $teamId,
        'input' => $input,
        'agent_order' => $agentOrder,
        'max_retries' => $maxRetries,
        'target_role' => $firstRole,
        'conversation' => [],
        'role_configs' => $roleConfigs,
        'skills_index' => $skillsIndex,
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
