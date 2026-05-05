# Media Manager Plugin — Audit & Rebuild Overview

**Status:** ✅ Audit + PRD + Architecture + Build Plan complete — ready to build  
**Last updated:** 2026-05-04

---

## 1. Background

An existing third-party WordPress plugin (`media-library-plus` by Max Foundry, v8.3.3) has been patched locally and is used to manage the physical folder structure of the WordPress Media Library. Rather than continue patching a 7,500-line monolithic file from an external vendor, the decision has been made to:

1. **Fully audit** the current plugin — features, settings, admin UI, database footprint, hooks, dependencies
2. **Produce a PRD** specifying the clean-room rebuild
3. **Build the new plugin from scratch** — modern, namespaced, modular, maintainable — under the name **Media Manager**

---

## 2. What We Know (Pre-Audit)

| Property | Detail |
|---|---|
| Plugin slug | `media-library-plus` |
| Vendor | Max Foundry (maxgalleria.com) |
| Version audited | 8.3.3 |
| Main file | `media-library-plus.php` — **~7,547 lines, fully monolithic** |
| Includes | 12 additional PHP files in `/includes/` |
| Architecture | Legacy — single God Class (`MGMediaLibraryFolders`), no namespacing |
| Purpose | Physical folder management for WordPress Media Library |
| Condition | Feature-rich but bloated; contains dead code, upsell scaffolding, and optional Pro features |
| Target rebuild | Modern OOP, namespaced (`MediaManager\*`), modular, WordPress-idiomatic |

---

## 3. Key Decisions (Pre-Audit)

| Topic | Decision |
|---|---|
| Folder model | **Physical** folders on disk — not virtual. This is the core value; keep as-is. |
| New plugin name | **Media Manager** |
| Frontend gallery / shortcodes | Out of scope for v1. Requires separate UX/workflow planning before committing. |
| Block Direct Access (BDA) | **Partial keep** — protected file directory and IP blocking only. Download links, expiry limits, and auto-protect dropped. |
| MaxGalleria integration | **Cut** entirely. |
| DB migration | **Clean slate.** New plugin installs fresh; no migration from old tables required. |
| Image SEO bulk tool | **Cut** — not needed. |
| Thumbnail regeneration | **Keep** as a feature. |
| User role access | Admins + **Editors**. Subscribers excluded. |

---

## 4. Audit Scope

Everything is in scope. Nothing is assumed to be kept without justification.

### 4a. Features & Functionality
- [x] Complete feature inventory — what exists, what's actively used
- [x] Feature categorization: core / keep / drop / redesign

### 4b. Admin & Settings
- [x] Settings pages — all options, types, defaults, and current usage
- [x] Admin screens and list table columns
- [x] User roles / capability checks

### 4c. Database Footprint
- [x] Custom tables — schema, indexes, relationships
- [x] `wp_options` keys — all stored options and transients
- [x] Post meta keys
- [x] Custom post types registered
- [x] Scheduled events

### 4d. Hook Inventory
- [x] All `add_action()` calls — hook name, callback, priority
- [x] All `add_filter()` calls — hook name, callback, priority
- [x] All AJAX endpoints (admin + nopriv)
- [x] Custom hooks exposed for extensibility

### 4e. External Dependencies
- [x] Bundled third-party libraries
- [x] External API integrations (none found)
- [x] Plugin dependencies

---

## 5. Planned Deliverables

| # | Document | Description |
|---|---|---|
| `00-overview.md` | **This file** | Project context, scope, decisions, status |
| `01-feature-inventory.md` | Feature audit | Full list with keep/drop/redesign decisions |
| `02-admin-audit.md` | Admin & settings audit | All settings, admin screens, capabilities |
| `03-database-audit.md` | DB footprint | Tables, options, post meta, CPT, scheduled events |
| `04-hook-inventory.md` | Hook map | All actions, filters, AJAX endpoints, custom hooks |
| `05-dependencies.md` | Dependencies | Bundled libs, external deps |
| `06-prd.md` | **Product Requirements Doc** | Full spec for the clean rebuild |
| `07-architecture.md` | New architecture | Namespace map, file structure, data model |
| `08-build-plan.md` | **Build plan** | Phase-by-phase execution plan with checklists |

---

## 6. Target Architecture (Principles)

The rebuild aligns with conventions established for the Basecamp theme and the Work/Events plugin rebuilds:

- **Namespaced PHP** — `MediaManager\Admin`, `MediaManager\Core`, `MediaManager\FileSystem`, `MediaManager\Security`, etc.
- **Single Responsibility** — one class per concern; no monolithic files
- **WordPress-idiomatic** — hook-driven, no direct DB writes outside model classes
- **Capability-aware** — `manage_options` (admin) + `edit_others_posts` (editor); no subscriber access
- **No vendor lock-in** — zero dependency on MaxFoundry, MaxGalleria, or any external plugin
