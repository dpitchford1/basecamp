---
name: code-review
description: Perform a comprehensive code review of a WordPress plugin built on the Basecamp theme stack. Use when reviewing a plugin, auditing code quality, or running a periodic plugin review. Covers PHP structure, security, repository pattern, assets (CSS/JS), and hygiene across Parts A (plugin), B (theme integration), C (assets), and D (hygiene).
disable-model-invocation: true
---

# Plugin Code Review

Read `references/checklist.md` before running any review steps.

## Usage Modes

| Mode | How |
|---|---|
| **Full review** | Run all parts A → B → C → D in sequence against the scoped directories. |
| **Part review** | Run one part at a time (e.g. "Review Part A") to keep responses focused and avoid truncation. |
| **Section review** | Run a single section (e.g. "Review A4 — Security") for a targeted deep dive. |
| **PR checklist** | Use section headers as a manual checklist when reviewing any pull request. |
| **Per-file review** | Prefix with "Review `{file path}` against:" and paste the relevant sections only. |

## Severity Legend

| Icon | Meaning |
|---|---|
| 🔴 | **Must fix** — violates a documented standard, introduces a bug, or is a security risk |
| 🟡 | **Should fix** — deviates from convention, may cause confusion or maintenance burden |
| 🟢 | **Suggestion** — improvement opportunity, not a violation |

## Output Format

For each finding include:

- **Severity:** 🔴 · 🟡 · 🟢
- **File path and line number(s)**
- **What the issue is**
- **What the standard requires**
- **Suggested fix (with code snippet where applicable)**

Group findings by checklist section (A1, A2, … D3).
