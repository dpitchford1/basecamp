# Plugin Code Review Checklist

Adapt all `{plugin-slug}`, `{Vendor}`, `{child-theme}`, and namespace map placeholders to the plugin under review before running.

---

**Perform a thorough code review of the `{plugin-slug}` plugin. The following directories are in scope — ignore everything else:**

```
/wp-content/plugins/{plugin-slug}/   ← Plugin source
/wp-content/themes/{child-theme}/    ← Child theme (Basecamp child)
/assets/                             ← CSS/JS/fonts/images (if repo root assets exist)
```

---

## PART A — PLUGIN (`wp-content/plugins/{plugin-slug}/`)

### A1. PHP Strict Typing & Class Structure

- Every `.php` file must have `declare(strict_types=1);` at the top.
- All classes must be `final` unless inheritance is explicitly required.
- All classes must be namespaced under `{Vendor}\{Plugin}\*` following the plugin's namespace map. Replace the namespace map below with the plugin's documented structure before running:
  - `Core` → Plugin, Loader, Activator, Deactivator, Updater
  - `Admin` → Meta boxes, list tables, settings, reports, docs
  - `Data` → Stateless repository classes (all DB queries)
  - `Frontend` → Shortcodes, template loader, public assets, public AJAX
  - `REST` → API endpoint classes
  - `Helpers` → Utility/helper classes
  - _(add plugin-specific namespaces as needed)_
- Verify the custom PSR-4 autoloader mapping: class names are CamelCase → `class-kebab-case.php`; interfaces use `interface-` prefix. Adding a new class at the correct path should autoload with no registration step.

---

### A2. Loader Pattern & Bootstrap Order

- All hook registrations in stateful classes **must** go through the `Loader` class — never call `add_action()`/`add_filter()` directly. Direct calls are acceptable **only** in static, self-contained modules with no state.
- `Loader::run()` must fire once at the end of `Plugin::run()`.
- New hooks must be added inside `Plugin::wire_hooks()`.
- Verify the bootstrap flow matches the plugin's documented phase order:
  1. Constants → Autoloader → activation/deactivation hooks
  2. `plugins_loaded` → `Plugin::run()` → `Updater::run()` (synchronous DB migrations)
  3. CPT registrations → Frontend → Admin (`is_admin()` gated) → Assets → Shortcodes → REST → AJAX
  4. `Loader::run()` → `do_action('{plugin_slug}_loaded')`

---

### A3. Data Integrity — Money & Timestamps

_(Skip or adapt this section if the plugin does not handle monetary values or custom date formatting.)_

- **All monetary values must be integer cents** — never floats. Flag any `floatval()`, `(float)`, or arithmetic that produces float intermediaries on money. A `MoneyHelper::format()` equivalent must be used for all display formatting.
- **All timestamps must be UTC Unix integers.** Date/time helpers must be used in all templates and admin views — never raw `date()`, `gmdate()`, or direct formatting.
- No hardcoded currency symbols, date formats, or timezone offsets — all must come from Settings or Helpers.

---

### A4. Security — Input, Output, Nonces, Capabilities

- **Output escaping:** Every `echo`, `printf`, and template variable must use `esc_html()`, `esc_attr()`, `esc_url()`, or `wp_kses_post()`. Check all HTML attributes and dynamic output.
- **Input sanitisation:** Every `$_GET`, `$_POST`, `$_REQUEST`, `$_COOKIE` access must be sanitised before use with `sanitize_text_field()`, `absint()`, `wp_unslash()`, etc.
- **AJAX endpoints:** All `wp_ajax_` and `wp_ajax_nopriv_` handlers must be nonce-verified (`wp_verify_nonce()` or `check_ajax_referer()`).
- **REST endpoints:** Every endpoint must have a proper `permission_callback`. Public endpoints use `__return_true` explicitly; admin endpoints must check capabilities.
- **Sensitive operations** (deletion, privilege escalation, data export): Must enforce capability checks both client-side and server-side.

---

### A5. Repository Pattern & Database Access

- **All DB queries must go through stateless repository classes** in `{Vendor}\{Plugin}\Data\`. Flag any direct `$wpdb`, `WP_Query`, `get_posts()`, or `get_post_meta()` calls found outside repository classes.
- Repository classes must be stateless — only `static` methods, no constructor, no instance state.
- Raw queries must use `$wpdb->prepare()`.
- Verify all repositories exist and cover their documented scope. Replace the list below with the plugin's documented repositories before running.

---

### A6. Custom Post Types, Taxonomies & Statuses

_(Populate this section with the plugin's documented CPTs, taxonomies, and post statuses before running.)_

- Verify all CPTs are registered with correct arguments (public/private, archive, supports, rewrite slug, capability type).
- Verify all custom taxonomies: hierarchical setting, public/private, rewrite slug.
- Verify all custom post statuses are registered and map to the correct lifecycle states.
- Verify any auto-derive rules (e.g. saving a post triggers related taxonomy term assignment).

---

### A7. Template System

- `Frontend::locate()` must check `{theme}/{plugin-slug}/{path}` before falling back to `{plugin-slug}/templates/{path}`.
- Every template file must have a docblock documenting available variables.
- Templates must not contain direct DB queries — all data must be prepared before the template is loaded.
- Verify all expected template directories and files exist per the plugin's documented template map.

---

### A8. Custom Workflow & Business Logic

_(Replace this section with the plugin's core workflow documentation before running.)_

- Verify the documented lifecycle/state machine is implemented correctly (e.g. post status transitions, capacity holds, cron cleanup).
- Verify all custom actions fire with the correct number and type of parameters as documented.
- Verify any scheduled events are registered correctly and fire at the expected intervals.
- Flag any workflow state that is partially implemented or inconsistent with documented behaviour.

---

### A9. REST API & Caching

- All endpoints must be registered under the plugin's documented namespace (e.g. `wp-json/{plugin}/v1/`).
- Every endpoint must accept only documented parameters; extra parameters must be ignored or rejected.
- Responses must be cached in WP object cache with appropriate expiry and a plugin-specific cache group.
- `GET /wp-json/{plugin}/v1/ping` must return `{"status": "ok"}`.

---

### A10. Settings System

- `Settings::get()` must check saved options → defaults → caller's fallback — in that order.
- All settings tabs must render and save independently.
- Verify all documented settings keys exist in the default settings array.
- Email templates (if any) must support all documented token replacements.

---

### A11. Activation, Deactivation & Uninstall

- **Activator::activate()** → `dbDelta()` for custom tables, seed default options, schedule cron events, set flush-rewrite flag.
- **Deactivator::deactivate()** → clear cron jobs only. Must not delete data.
- **uninstall.php** → drop custom tables, delete all plugin `wp_options` rows, remove all plugin CPT posts.
- `Updater::run()` must fire on every load, compare the stored DB version, run migrations sequentially. Schema changes go in migrations — **never** modify `Activator::create_tables()` for existing installs.

---

### A12. Shortcodes & Frontend Features

_(Populate this section with the plugin's documented shortcodes and frontend features before running.)_

- Verify all registered shortcodes render correctly for both authenticated and guest users.
- Shortcodes must gate output appropriately based on user state (logged in / guest / capability).
- Verify shortcode output is fully escaped.

---

## PART B — BASECAMP THEME INTEGRATION (`wp-content/themes/{child-theme}/`)

### B1. Module System & Bootstrap

- `functions.php` is the sole bootstrap. Every feature is a `require_once` in dependency order: Core → Settings → Frontend → Admin → SEO → Theme Functions → WebP → REST → Cron → Dev → WooCommerce.
- Toggling a feature = commenting out its `require_once` line; nothing else should need to change.
- WooCommerce is disabled by default — verify its `require_once` is commented out unless explicitly enabled.
- Development tools (`class-basecamp-development.php`) must only load when `WP_ENVIRONMENT_TYPE === 'local'` or `REMOTE_ADDR` is `127.0.0.1` / `::1`.

---

### B2. Namespaces & Aliases

- All theme classes must use `Basecamp\<Area>` namespaces with PascalCase.
- Back-compat aliases must be declared in `functions.php` for templates using static method calls (e.g. `class_alias('Basecamp\Admin\Settings', 'Basecamp_Settings')`).
- WP core classes inside a namespace must be prefixed with `\` (e.g. `\WP_Query`, `\WP_Post`).

---

### B3. Theme Settings

- `Basecamp_Settings::get('key')` must be the single read path. Stored as serialized option `basecamp_theme_settings`.
- Verify all documented settings keys exist and fall back correctly.
- GA ID override via `BASECAMP_GA_MEASUREMENT_ID` constant must work.

---

### B4. Plugin/Theme Bridge

- **All theme/plugin bridge code must live in the plugin** (e.g. `includes/Core/class-theme-integration.php`), not in the theme's `functions.php`.
- Schema graphs added by the plugin must use `add_filter('basecamp_schema_graphs', ...)`.
- SEO titles for plugin CPTs must be handled by an extension class registered on `Basecamp\SEO\TitleManager::$extensions`.

---

### B5. Template HTML Quality

- HTML must be semantic, accessible, and bloat-free. Verify proper use of landmarks, heading hierarchy, alt attributes, ARIA attributes where needed.
- BEM naming for CSS classes (e.g. `card__picture`, `hero__img`).
- Verify default WP frontend output is disabled via `inc/frontend/remove-bloat.php`.

---

## PART C — ASSETS (`/assets/` or plugin `assets/`)

### C1. CSS / SCSS

- SCSS uses Dart Sass `@use` / `@forward` — **not** `@import`. Flag any `@import` usage.
- Barrel files must namespace partials: `@forward "header/global-header" as basecamp-header-*;`.
- **All breakpoint mixin calls must be centralized** in `assets/css/scss/basecamp-base-layout/_responsive.scss`. Flag any `@include bp-*()` found inside component files.
- Breakpoints are max-width: `bp-480`, `bp-600`, `bp-768`, `bp-920`, `bp-1024`, `bp-1280`, `bp-1440`.
- No CSS frameworks — all styles are hand-coded.
- Both `.scss` source and compiled `.min.css` output must be committed. No build pipeline — Live Sass Compiler handles compilation.
- Never commit manually minified code.
- Theme CSS is loaded via raw `<link>` tags in `header.php` — **not** `wp_enqueue_style`. This is intentional for per-template performance control. Plugin CSS uses `wp_enqueue_style`.
- Critical CSS must be inlined via `Basecamp_Frontend::output_critical_css()`.

---

### C2. JavaScript

- Frontend JS must be minimal and purposeful. Flag any enqueued frontend JS that isn't documented as intentional.
- jQuery is acceptable in **WP Admin only** — flag any jQuery usage on the frontend.
- When JS is written, it must follow the project's documented conventions:
  - IIFE module pattern with `"use strict"` at the top of every IIFE
  - DOM caching with `const` at module top — no re-querying in loops or handlers
  - Guard before every DOM interaction (`elementExists(el)`)
  - `document.readyState` check before `init()`, fallback to `DOMContentLoaded`
  - Progressive enhancement with feature detection and fallbacks
  - Accessibility: `aria-expanded`, `aria-hidden`, focus trapping, keyboard navigation
  - `throttle()` for scroll/resize handlers, `debounce()` for input/search handlers
  - JSDoc blocks on every function
  - Source `.js` alongside `.min.js` — Auto-Minify generates the minified file on save

---

## PART D — CODE HYGIENE (applies to all in-scope directories)

### D1. Forbidden Patterns

- No `error_log()` calls in committed code
- No `var_dump()`, `print_r()`, or `dd()` debug output
- No manually minified files (minification is handled by VS Code extensions)
- No ACF — field data is stored as standard post meta via custom admin UI
- No block editor / Gutenberg registration (`register_block_type`, `@wordpress/blocks`)
- No CSS frameworks
- No ES modules, bundlers, or transpilation

---

### D2. Naming Conventions

| Thing | Convention | Example |
|---|---|---|
| Theme PHP classes | `Basecamp\<Area>` namespace, PascalCase | `Basecamp\SEO\Schema` |
| Plugin PHP classes | `{Vendor}\{Plugin}\<Area>` namespace, PascalCase | `MyPlugin\Data\PostRepository` |
| Global functions | `basecamp_*` (theme), `{plugin_slug}_*` (plugin) | `basecamp_get_link_list()` |
| Hooks | `basecamp_*` (theme), `{plugin_slug}_*` (plugin) | `basecamp_schema_graphs` |
| Text domains | `basecamp` (theme), `{plugin-slug}` (plugin) | |
| CSS classes | BEM | `card__picture`, `hero__img` |
| WP core classes inside a namespace | Prefix with `\` | `\WP_Query`, `\WP_Post` |

---

### D3. Build Status Awareness

- Note the current build stage of the plugin before running — early-stage builds may have intentionally stubbed or paused functionality (e.g. payments, frontend JS, custom roles). Do not suggest activating stubbed features unless explicitly requested.
- The goal is **simple, clean, efficient, understandable code** — flag any over-engineering.
