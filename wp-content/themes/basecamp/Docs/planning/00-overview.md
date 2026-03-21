# Planning Overview

## What Basecamp Is

Basecamp is a WordPress starter theme built as a personal and professional foundation — a clean, opinionated base that can be downloaded, installed, and built upon without fighting the tool.

It is not a framework. It is not a page builder. It is not feature-complete out of the box for any one project. It is a starting point with sensible defaults, a clear architecture, and the scaffolding already in place so the first hours of a new project are spent building rather than cleaning.

---

## Core Principles

### Performance
- Minimal asset footprint — only what the page needs is loaded
- No jQuery dependency in theme code
- WebP image conversion built in
- Aggressive bloat removal (heartbeat, oEmbed, resource hints, emoji scripts)
- CSS loaded via root-relative paths for full control; no blind `wp_enqueue_style` for frontend styles

### Extensibility
- Hook-first architecture — behavior is added via `add_action` / `add_filter`, never inline in templates
- Module pattern — every feature is a self-contained file; toggle by commenting a single `require_once`
- Sensible filter points throughout so child themes or plugins can alter behavior without hacking core files
- WooCommerce support scaffolded and toggle-ready

### Modularization
- `inc/` is organized by concern: `admin/`, `frontend/`, `seo/`, `img-optimization/`, `rest/`, `core/`, `development/`
- Each module boots itself (via `::init()` or constructor) — `functions.php` only wires the load order
- Admin-only code is gated behind `is_admin()`; dev tools behind IP check

### Security
- Output consistently escaped (`esc_html`, `esc_url`, `esc_attr`, `wp_kses_post`)
- Nonces on any form/AJAX interaction
- Path traversal protection in the docs viewer
- Cookie consent with GA Consent Mode v2 — no analytics fire before explicit consent
- Auto-updates disabled (theme and plugins) — deployments are intentional

### Sensible Defaults Over Plugin Overuse
- SEO handled natively (titles, meta descriptions, Open Graph, Twitter Card, JSON-LD schema) — defers to Yoast/Rank Math if present
- Google Analytics loaded conditionally (`BASECAMP_ENV`-based) with cookie consent gating; ID configured via Theme Settings
- Cookie consent banner built in — no plugin needed
- Schema.org structured data output natively
- Classic editor enforced — no Gutenberg friction

---

## Who It Is For

- **Primarily:** The author — a reliable, maintained starting point for client and personal projects
- **Secondarily:** Any developer who wants a clean, well-documented, opinionated WordPress starter that doesn't assume a plugin for every problem

---

## What It Is Not

- Not a theme for end users to configure via the Customizer
- Not a block theme
- Not a general-purpose theme with every layout option imaginable
- Not dependent on any particular page builder, slider plugin, or third-party service

---

## Key Files at a Glance

| File | Purpose |
|---|---|
| `functions.php` | Bootstrap — loads all modules in order |
| `inc/class-basecamp.php` | Core setup: menus, image sizes, theme supports |
| `inc/frontend/class-basecamp-frontend.php` | Output buffering, menu helpers, schema, utilities |
| `inc/frontend/remove-bloat.php` | Strips WP default cruft |
| `inc/frontend/class-basecamp-cookie-consent.php` | GDPR/CCPA banner + GA Consent Mode v2 |
| `inc/admin/class-basecamp-admin.php` | Admin UX: login, dashboard, editor tweaks |
| `inc/admin/class-basecamp-docs.php` | In-admin Markdown documentation viewer |
| `inc/admin/class-basecamp-settings.php` | Theme Settings page — GA, compliance, feature flags |
| `inc/seo/` | Title, meta, social, schema — all native |
| `inc/img-optimization/` | WebP conversion on upload + batch processing |
| `inc/theme-functions/basecamp-analytics.php` | Conditional GA4 loader |
| `Docs/` | Living documentation — planning, developer reference, content guides |


