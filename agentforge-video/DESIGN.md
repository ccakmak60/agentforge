# AgentForge — Video Design

## Style Prompt

Deconstructed (Neville Brody) — industrial, raw, punk developer-launch energy. Dark grey canvas, rust-orange accent, raw white type. Blocky display headlines slam into frame; code-rail monospace carries metadata and pipeline labels. Glitch chromatic jitters between scenes, a single staggered-block wipe at the topic break. Nothing feels polished — edges bleed, type escapes the frame, hairlines cut the composition.

## Colors

- `#1a1a1a` — background (dark grey, the canvas)
- `#f0f0f0` — foreground (raw white, primary text)
- `#D4501E` — rust orange (accent, slams, rails, rules — use for 60px+ or bold labels)
- `#3a3a3a` — structural grey (ghost text, hairlines, grid)
- `#8a8a8a` — muted grey (meta labels, metadata rails)

## Typography

- **Archivo Black** (900) — display headlines, slams, wordmark. Blocky industrial voice.
- **JetBrains Mono** (400 / 700) — labels, metadata, terminal, pipeline annotations. Developer-register voice.

Tension: display sans heavy vs. engineering mono. Headlines shout; mono narrates the system log.

## Motion

- Entrances: `back.out(2.5)`, `expo.out`, `power4.out`, `elastic.out(1.2, 0.4)`, `steps(6)` for scrambles.
- Scrubs: text x-jitter on scene holds (subtle ambient motion).
- Transitions: **Glitch** (primary, 4 of 5 scene changes) + **Staggered blocks** (1 topic break, s3→s4).
- No exit animations except on final scene (fade to near-black).

## What NOT to Do

- No gradient text, no cyan, no purple, no neon-tech palette.
- No centered stacks floating in empty space — anchor content to edges, pin hairlines.
- No smooth `power2.out` for everything — Deconstructed demands overshoots, slams, and `steps()` scrambles.
- No rounded corners anywhere. Everything is square-cut.
- No thin 400-weight headlines — this style lives at 900.

## Scene Plan (45s, 1920x1080)

| # | Time       | Beat         | Content                                                          |
| - | ---------- | ------------ | ---------------------------------------------------------------- |
| 1 | 0.0–6.0    | Cold open    | "ONE TASK. ONE AGENT? NOT ANYMORE." system-log kicker            |
| 2 | 6.2–13.0   | Brand reveal | AGENT/FORGE split wordmark + tagline                             |
| 3 | 13.2–22.0  | Pipeline     | RESEARCHER → SUMMARIZER → REVIEWER with rust connectors          |
| 4 | 22.55–31.0 | Stack        | SQS · n8n · K8s · DynamoDB · MinIO · Terraform grid              |
| 5 | 31.2–38.0  | Numbers      | 3 roles · 1 queue · ∞ tasks · 0 compromises                      |
| 6 | 38.2–45.0  | CTA          | `docker compose up` terminal + github call-to-action, fade close |

Transitions: glitch (1→2, 2→3, 4→5, 5→6), staggered blocks (3→4, topic change).
