# Events Plugin — Audit & Rebuild Overview

**Status:** � Audit complete — all five audit documents produced  
**Last updated:** 2026-03-21

---

## 1. Background

An existing WordPress events management plugin has grown into a monolithic structure over time. Rather than incrementally refactor the existing codebase (high complexity, high risk of regression), the decision has been made to:

1. **Fully audit** the current plugin — features, settings, admin UI, database footprint, hooks, dependencies
2. **Produce a PRD** specifying the clean-room rebuild
3. **Build the new version** from scratch — modern, namespaced, modular, maintainable

---

## 2. What We Know (Pre-Audit)

| Property | Detail |
|---|---|
| Type | WordPress plugin |
| Domain | Events management |
| Scale | Main file ~2,500 lines (monolithic) |
| Architecture | Legacy — single-file or minimally split |
| Condition | Feature-rich but bloated; needs pairing down |
| Target rebuild | Modern OOP, namespaced, modular |

---

## 3. Audit Scope

Everything is in scope. Nothing is assumed to be kept without justification.

### 3a. Features & Functionality
- [x] Complete feature inventory — what exists, what's actively used
- [x] Feature categorization: core / nice-to-have / dead weight
- [x] User-facing vs. admin-only features
- [x] Shortcodes, blocks, or template tags exposed to content editors

### 3b. Admin & Settings
- [x] Settings pages — all options, their types, defaults, and current usage
- [x] Meta boxes — which post types, what fields
- [x] Custom admin screens or list table columns
- [x] User roles / capability checks

### 3c. Database Footprint
- [x] Custom tables — schema, indexes, relationships
- [x] `wp_options` keys — all stored options and transients
- [x] Post meta keys — per post type
- [x] Custom post types and taxonomies registered
- [x] Any direct `$wpdb` queries (raw SQL audit)

### 3d. Hook Inventory
- [x] All `add_action()` calls — hook name, callback, priority
- [x] All `add_filter()` calls — hook name, callback, priority
- [x] Custom hooks exposed for extensibility (`do_action`, `apply_filters`)

### 3e. Frontend Output
- [x] Enqueued scripts and styles (and their conditions)
- [x] Template files or template hierarchy overrides
- [x] AJAX endpoints
- [x] REST API endpoints (if any)

### 3f. External Dependencies
- [x] Third-party libraries (bundled or Composer)
- [x] External API integrations (payment, maps, email, etc.)
- [x] Plugin dependencies (`is_plugin_active()` checks)

### 3g. Data Flow
- [x] How an event is created → stored → displayed
- [x] Registration/RSVP flow (if present)
- [x] Any email/notification triggers

---

## 4. Planned Deliverables

| # | Document | Description |
|---|---|---|
| `00-overview.md` | **This file** | Project context, scope, status |
| `01-feature-inventory.md` | Feature audit | Full list with keep/drop/redesign decisions |
| `02-admin-audit.md` | Admin & settings audit | All settings, meta boxes, admin screens |
| `03-database-audit.md` | DB footprint | Tables, options, post meta, CPTs, taxonomies |
| `04-hook-inventory.md` | Hook map | All actions, filters, custom hooks |
| `05-dependencies.md` | External dependencies | Libraries, APIs, plugin deps |
| `06-prd.md` | **Product Requirements Doc** | Full spec for the clean rebuild |
| `07-architecture.md` | New architecture | Namespace map, file structure, data model |

---

## 5. Target Architecture (Principles)

The rebuild should align with the same conventions established for the Basecamp theme:

- **Namespaced PHP** — `PluginName\Admin`, `PluginName\Frontend`, `PluginName\Core`, etc.
- **Single Responsibility** — one class per concern, no monolithic files
- **WordPress-idiomatic** — hook-driven, no direct DB writes outside model classes
- **REST-first where async** — AJAX replaced with WP REST API endpoints under `plugin-name/v1`
- **No bundled dead weight** — only dependencies actually needed ship in the plugin
- **Upgrade path** — migration routine for existing data (CPT slugs, option keys, table schemas)

---

## 6. Process

```
[ ] Plugin added to workspace
[ ] Static analysis pass (grep for patterns, count hooks/options/CPTs)
[ ] Feature inventory documented (01-feature-inventory.md)
[ ] Admin audit documented (02-admin-audit.md)
[ ] Database audit documented (03-database-audit.md)
[ ] Hook inventory documented (04-hook-inventory.md)
[ ] Dependencies documented (05-dependencies.md)
[ ] PRD drafted (06-prd.md)
[ ] Architecture designed (07-architecture.md)
[ ] Rebuild begins
```

---

## 7. Notes

- Plugin will be added to `wp-content/plugins/` in this workspace
- Audit will be performed via static analysis (grep, read) — no runtime required
- PRD is the gate before any rebuild code is written
