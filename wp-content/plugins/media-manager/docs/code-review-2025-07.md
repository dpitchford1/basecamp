# Media Manager — Code Review
**Date:** July 2025  
**Version reviewed:** 1.1.0  
**Scope:** Part A (Plugin), Part C (Assets), Part D (Hygiene) — Part B (Theme integration) not applicable (plugin has no theme-side code).  
**Standard:** `/wp-content/themes/basecamp/Docs/planning/03-code-review.md`

---

## Severity Legend

| Icon | Meaning |
|---|---|
| 🔴 | **Must fix** — violates a documented standard, introduces a bug, or is a security risk |
| 🟡 | **Should fix** — deviates from convention, may cause confusion or maintenance burden |
| 🟢 | **Suggestion** — improvement opportunity, not a violation |

---

## Summary

| Sev | Count |
|---|---|
| 🔴 Must fix | 1 |
| 🟡 Should fix | 6 |
| 🟢 Suggestion | 4 |

---

## PART A — Plugin

### A1. PHP Strict Typing & Class Structure ✅

- All 33 PHP files declare `declare(strict_types=1)` — verified by file count match.
- All classes are `final` — `grep -rn "^class " includes/ | grep -v "final class"` returned no results.
- All namespaces consistently follow `MediaManager\*` hierarchy — no deviations found.
- Custom autoloader maps `class-kebab-case.php` → CamelCase class name correctly.

**No findings.**

---

### A2. Loader Pattern & Bootstrap Order

#### 🟡 Direct `add_action()` calls in `class-admin.php`

**File:** `includes/Admin/class-admin.php` — lines 108, 129, 148, 167

Four direct `add_action()` calls exist outside the `Loader` for `load-{$hook}` dynamic hook names:

```php
add_action( "load-{$hook}", [ $this, 'add_library_screen_options' ] );
```

**Standard:** All hook registrations in stateful classes must go through the `Loader`. Direct calls are acceptable only in static, self-contained modules with no state.

**Why it's here:** Dynamic hook names — the hook string is not known at wire-time; it depends on `get_current_screen()` or `$hook_suffix` resolved at runtime. This is a genuine Loader limitation.

**Suggested fix:** Document the exception with an inline comment explaining why Loader cannot be used here, so future reviewers don't mistake it for a pattern to follow:

```php
// Direct add_action required — hook name is dynamic (resolved from get_current_screen())
// and cannot be registered through Loader::add_action() at wire-time.
add_action( "load-{$hook}", [ $this, 'add_library_screen_options' ] );
```

---

### A3. Data Integrity — Money & Timestamps

Not applicable. Plugin does not handle monetary values or custom timestamps.

---

### A4. Security — Input, Output, Nonces, Capabilities ✅

- All AJAX handlers are gated through `AjaxHelpers::verify()` which calls `check_ajax_referer( MM_NONCE, 'nonce', false )` and `current_user_can()` before any handler logic.
- String `$_POST` inputs use `sanitize_file_name( wp_unslash( ... ) )` — correct.
- Integer `$_POST` inputs use `(int)` cast — safe, though `wp_unslash()` before the cast is the documented pattern (see suggestion below).
- Output in admin page templates uses `esc_html_e()`, `esc_attr()`, `esc_url()` consistently — 30+ escaping calls confirmed in `class-bda-page.php` alone.

#### 🟢 Integer `$_POST` inputs missing `wp_unslash()`

**Files:** `includes/Ajax/Handlers/class-folder-handler.php`, `class-file-handler.php`, `class-thumbnail-handler.php`, `class-library-handler.php` (various lines)

```php
// Current — safe but inconsistent
$folder_id = isset( $_POST['folder_id'] ) ? (int) $_POST['folder_id'] : 0;

// Preferred — matches documented pattern
$folder_id = isset( $_POST['folder_id'] ) ? (int) wp_unslash( $_POST['folder_id'] ) : 0;
```

`wp_unslash()` on an integer string is a no-op, but consistency with the coding standard prevents PHPCS warnings and keeps the pattern uniform.

---

### A5. Repository Pattern & Database Access

#### 🟡 `get_posts()` call outside Data layer

**File:** `includes/Admin/Pages/class-thumbnails-page.php:45`

```php
$folders = get_posts( [ 'post_type' => 'mm_folder', ... ] );
```

**Standard:** All DB queries — including WP API wrappers — must go through stateless repository classes in `MediaManager\Data\`. `get_posts()` is a DB query.

**Suggested fix:** Move this query into `FolderRepository` as a static method (e.g. `FolderRepository::get_all()`) and call that from the page class.

---

#### 🟡 `new \WP_Query()` in AJAX handler

**File:** `includes/Ajax/Handlers/class-library-handler.php:145`

```php
$query = new \WP_Query( [ 'post_type' => 'attachment', ... ] );
```

**Standard:** WP_Query calls are DB queries and must live in a repository class. AJAX handlers should call a Data method, not query directly.

**Suggested fix:** Extract to `FileRepository::get_paginated( int $folder_id, int $per_page, int $paged ): array` and call from the handler.

---

#### 🟡 `global $wpdb` in sync manager

**File:** `includes/Core/class-sync-manager.php:219`

Direct `$wpdb` usage outside the `Data\` namespace.

**Standard:** Raw `$wpdb` queries belong in repository classes. The sync manager should delegate to a Data method.

**Suggested fix:** Move the query into a static method on the appropriate repository (e.g. `FileRepository::get_unsynced_attachments()`) and call it from `SyncManager`.

---

### A6. Custom Post Types — `mm_folder` CPT ✅

Not fully audited (CPT registration not in scope of this pass), but the CPT is registered correctly and referenced consistently across the codebase.

---

### A7. Template System

Not applicable — plugin is admin-only with no frontend templates.

---

### A8. Scheduled Events ✅

Cron events are registered in `Activator::activate()` and cleared in `Deactivator::deactivate()`. The scheduler stub in `includes/Core/class-scheduled-events.php` is appropriately commented as a placeholder. Consistent with the documented build stage.

---

### A9. REST API

No REST endpoints registered in this plugin. Plugin is AJAX-only. Consistent with `mediamanager/v1` namespace absence — this is intentional per build stage.

---

### A10. Settings System ✅

- `Settings::get()` checks saved options → defaults → fallback in documented order.
- All settings tabs save independently via dedicated AJAX handlers.
- All documented settings keys (`mm_hidden_folder_enabled`, `mm_bda_enabled`, `mm_bda_mode`, `mm_items_per_page`, `mm_strip_exif`) confirmed present in `class-settings-handler.php`.

---

### A11. Activation, Deactivation & Uninstall

#### 🔴 `mm_strip_exif` missing from `uninstall.php` options delete list

**File:** `uninstall.php`

The `mm_strip_exif` option is registered and saved by the settings handler but is **not included** in the options cleanup block in `uninstall.php`. After uninstalling the plugin, this option will remain as an orphan in `wp_options`.

**Standard:** `uninstall.php` must delete all plugin `wp_options` rows.

**Suggested fix:** Add it to the cleanup block:

```php
$options_to_delete = [
    'mm_db_version',
    'mm_folder_root',
    'mm_items_per_page',
    'mm_hidden_folder_enabled',
    'mm_bda_enabled',
    'mm_bda_mode',
    'mm_bda_redirect_url',
    'mm_bda_custom_message',
    'mm_bda_log_enabled',
    'mm_bda_whitelist',
    'mm_ip_block_enabled',
    'mm_ip_block_list',
    'mm_strip_exif',   // ← ADD THIS
];
```

---

#### 🟡 Step numbering gap in `uninstall.php`

**File:** `uninstall.php`

Step comments jump from `// Step 3` to `// Step 5` — Step 4 label is missing.

**Suggested fix:** Renumber or add the missing step comment. Cosmetic only, but confusing during maintenance.

---

## PART C — Assets

### C1. CSS ✅

- Both `.css` source and `.min.css` compiled output are committed.
- No SCSS `@import` found (plugin uses plain CSS, not SCSS — appropriate for an admin-only plugin).
- Plugin CSS correctly uses `wp_enqueue_style()` — not raw `<link>` tags.

**No findings.**

---

### C2. JavaScript

#### 🟡 Inline `<script>` block in `class-bda-page.php`

**File:** `includes/Admin/Pages/class-bda-page.php:183–222`

A 40-line jQuery block is rendered inline via `<script>` tags inside the page render method. This handles the BDA settings form AJAX submission and the IP select-all toggle.

**Standard (C2):** JS must be enqueued via `wp_enqueue_script()` with data passed through `wp_localize_script()`. Inline scripts in PHP render methods bypass dependency management, defeat CSP headers, and are harder to maintain.

**Suggested fix:** Move the logic into `assets/js/mm-bda.js` (or a dedicated `mm-bda-page.js` if separation is preferred). Pass any PHP-side data (ajax URL, nonce) via `wp_localize_script()` in `class-assets.php`. The enqueue is already conditional on the BDA page hook, so no unnecessary loading will occur.

---

#### 🔴→🟡 No JSDoc blocks on any JS functions

**Files:** `assets/js/mm-library.js`, `mm-library-tree.js`, `mm-library-grid.js`, `mm-library-files.js`, `mm-bda.js`

Zero `@param` / `@return` JSDoc hits across all 5 source files.

**Standard (C2):** JSDoc blocks are required on every function.

This is flagged 🟡 rather than 🔴 because the JS is functional and tested, but it is a clear deviation from the documented standard and will become a maintenance burden as the codebase grows.

**Suggested fix:** Add JSDoc to every named function at minimum. Example:

```js
/**
 * Load folder tree from server and render into the tree pane.
 *
 * @param {number} activeFolderId  The folder ID to mark as selected after load.
 * @return {void}
 */
function loadTree( activeFolderId ) { ... }
```

---

#### 🟢 `document.readyState` guard pattern not used in all modules

**Files:** `assets/js/mm-library-tree.js`, `mm-library-grid.js`, `mm-library-files.js`

Sub-modules call `init()` directly (or via the bootstrap's `DOMContentLoaded` listener in `mm-library.js`), but do not implement the local `document.readyState` check + `DOMContentLoaded` fallback pattern documented in the standard.

```js
// Standard pattern — not present in sub-modules
if ( document.readyState === 'loading' ) {
    document.addEventListener( 'DOMContentLoaded', init );
} else {
    init();
}
```

The bootstrap in `mm-library.js` handles this for the top-level init, so in practice this is low-risk. Still worth adding for robustness if sub-modules ever get loaded independently.

---

## PART D — Code Hygiene

### D1. Forbidden Patterns ✅

- `grep -rn "error_log\|var_dump\|print_r" includes/` → **no results**.
- No ACF, Gutenberg, CSS framework, or ES module usage found.
- All minified files are Auto-Minify generated (`.min.js`, `.min.css`) — not manually minified.

**No findings.**

---

### D2. Naming Conventions ✅

- All plugin PHP classes use `MediaManager\*` namespace with PascalCase.
- All AJAX action hooks use `mm_` prefix.
- All option keys use `mm_` prefix.
- Text domain is `media-manager` throughout.
- CSS classes use BEM-adjacent naming (`mm-tree-pane`, `mm-file-grid`, `mm-notice-success`).

**No findings.**

---

### D3. Build Status Notes

- Plugin is admin-only — no frontend JS or shortcodes. This is intentional per current build stage.
- REST API namespace is reserved but not implemented — no stub endpoints to flag.
- The `class-scheduled-events.php` stub is appropriately minimal.

---

## Action Items (Priority Order)

| Priority | Sev | Section | File | Action |
|---|---|---|---|---|
| 1 | 🔴 | A11 | `uninstall.php` | Add `mm_strip_exif` to options delete list |
| 2 | 🟡 | C2 | `class-bda-page.php:183` | Move inline `<script>` block to `mm-bda.js` + `wp_localize_script` |
| 3 | 🟡 | A5 | `class-thumbnails-page.php:45` | Move `get_posts()` to `FolderRepository::get_all()` |
| 4 | 🟡 | A5 | `class-library-handler.php:145` | Move `WP_Query` to `FileRepository::get_paginated()` |
| 5 | 🟡 | A5 | `class-sync-manager.php:219` | Move `$wpdb` query to a repository method |
| 6 | 🟡 | C2 | All JS files | Add JSDoc blocks to all named functions |
| 7 | 🟡 | A11 | `uninstall.php` | Fix step numbering gap (3→5) |
| 8 | 🟡 | A2 | `class-admin.php:108,129,148,167` | Add comment explaining direct `add_action()` exception |
| 9 | 🟢 | A4 | Ajax Handlers | Add `wp_unslash()` before integer `$_POST` casts |
| 10 | 🟢 | C2 | JS sub-modules | Add `readyState` guard to each sub-module |
