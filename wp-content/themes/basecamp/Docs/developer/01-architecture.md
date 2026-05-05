# Theme Architecture

Basecamp is a custom WordPress **parent theme** built for performance, clarity, and control. It provides all shared infrastructure — module system, SEO, image optimisation, admin UX, REST endpoints, dev tools — to any number of child themes. Each child theme inherits everything and adds only project-specific templates, assets, and feature modules.

---

## Parent / Child Design

| Layer | What lives here |
|---|---|
| **Basecamp (parent)** | All shared modules, no project-specific code |
| **Child theme** | Project templates, project assets, project-specific PHP modules |

Basecamp must never contain code that only applies to one project. Project-specific CPTs, custom metaboxes, page templates, and `add_theme_support()` calls for project CPTs all belong in the child.

The child theme's `style.css` declares `Template: basecamp`. WordPress loads the parent `functions.php` automatically before the child's — no manual parent bootstrap needed.

**Child theme `functions.php` rules:**
- `require_once` new project modules (never re-require parent modules)
- Declare global template functions at the file root (not inside a namespace)
- Wire `add_theme_support()` / `add_image_size()` extensions via `after_setup_theme`
- Call `Basecamp\Admin\Settings::get()` freely — parent loads it first

---

## Key Principles

- **Lightweight by design** — only assets each page actually needs are loaded
- **Hook-first** — behavior is added via `add_action` / `add_filter` in module files, never inline in templates
- **No build pipeline** — SCSS compiled by Live Sass Compiler (VS Code), JS minified by Auto-Minify
- **Text domain** — all translatable strings use `basecamp`

---

## Directory Structure

```
wp-content/themes/basecamp/
  functions.php              Theme bootstrap — loads all modules
  style.css                  WordPress theme header only (no styles)

  inc/
    class-basecamp.php       Core setup: menus, image sizes, theme supports
    admin/
      class-basecamp-admin.php      Admin UX: login, dashboard, TinyMCE, bar
      basecamp-admin-helpers.php    Sanitizers, upload tweaks
      class-basecamp-settings.php   Theme Settings page
      class-basecamp-docs.php       In-admin Docs viewer
      class-basecamp-page-theme.php Page → Theme assignment (column + meta box)
      basecamp-media.php            Auto-title stripping, bulk-clear tool
    core/
      basecamp-scheduled-events.php Cron stubs
    development/
      class-basecamp-development.php DevPilot drawer (local only)
      development-pilot.php          Drawer template
      template.php                   get_current_template() helper
      css/ js/                       Drawer assets (bundled in theme)
    frontend/
      class-basecamp-frontend.php   output_critical_css(), page_navi(), related_posts()
      class-basecamp-svg-icons.php  SVG icon registry
      remove-bloat.php              Dequeues WP cruft
      class-basecamp-toast.php      Dismissable announcement bar
      basecamp-page-helpers.php     Page conditional helpers
      basecamp-subnav.php           Contextual child/sibling subnav
    img-optimization/
      basecamp-webp-functions.php   Frontend WebP URL substitution
      basecamp-webp-conversion.php  Upload-time JPEG/PNG → WebP
      basecamp-thumb-regen.php      Thumbnail regeneration tool
      webp-test-admin.php           WebP capability test (Image Tools tab)
    rest/
      basecamp-rest-endpoints.php   Routes under basecamp/v1
    seo/
      class-basecamp-seo.php        SEO bootstrap
      basecamp-title-functions.php  Context-aware <title>
      basecamp-meta-description-functions.php
      basecamp-social-meta-functions.php  Open Graph + Twitter Card
      class-basecamp-schema.php     JSON-LD structured data
    theme-functions/
      basecamp-meta-link-list.php   Link list repeater meta box
      basecamp-analytics.php        GA4 integration
      basecamp-cpt-scaffold.php     CPT scaffold (disabled by default)
      basecamp-category-url.php     Category URL rewrite (disabled by default)
    woocommerce/
      woocommerce-functions.php     WooCommerce theme support (disabled by default)

  template-parts/            Reusable template partials

  Docs/
    content-team/            CMS guides for content editors
    developer/               Technical reference for developers
    planning/                Project roadmap and todos
```

---

## Asset Pipeline

Assets live at the **repo root** `/assets/` (not inside the theme). This allows fine-grained, template-level loading for performance.

```
assets/
  css/
    scss/           SCSS source files
    build/          Compiled .min.css (Live Sass Compiler output)
    resources/      Third-party CSS (Swiper, video player)
  js/
    resources/      Vanilla JS + minified versions
    build/          (reserved)
  fonts/
  img/
```

CSS is referenced in `header.php` via root-relative paths (`/assets/css/build/...`), not via `wp_enqueue_style`.

---

## Module Loading (`functions.php`)

Modules are loaded in this order:

1. **Theme Settings** (`inc/admin/class-basecamp-settings.php`) — loaded first so `Basecamp\Admin\Settings::get()` is available everywhere (aliased as `Basecamp_Settings`)
2. **Core theme class** (`inc/class-basecamp.php`) — theme supports, menus, image sizes
3. **Frontend classes** — SVG icons, frontend helpers, remove-bloat, cookie consent, toast, page helpers, subnav
4. **Admin-only modules** (wrapped in `is_admin()`) — Admin UX, helpers, media, docs viewer, **Page→Theme assignment**
5. **SEO** (`class-basecamp-seo.php` bootstraps title, meta, social, schema)
6. **Theme functions** — link list meta box, analytics; CPT scaffold and category URL rewrite disabled by default
7. **Image optimisation** — WebP + thumbnail regen; skipped when disabled in Theme Settings
8. **REST endpoints**
9. **Scheduled events** (cron)
10. **Development tools** — gated on `WP_ENVIRONMENT_TYPE === 'local'` with IP fallback
11. **Ecommerce** — disabled by default; uncomment to activate

---

## Hooks Strategy

- Registration hooks (`init`) stay in dedicated files — no anonymous closures for public APIs
- Filters always have a `@return` docblock
- Hook priorities follow this convention:
  - Priority 1: Google Search Console verification meta tag
  - Priority 4: Cookie consent defaults (before GA at 5)
  - Priority 5: Google Analytics snippet
  - Priority 10: Default (most theme hooks)

---

## Environment Detection

- **Dev tools** — `Basecamp\Development\Development` loads when `WP_ENVIRONMENT_TYPE === 'local'` (primary check) or `REMOTE_ADDR` is `127.0.0.1` / `::1` / `::ffff:127.0.0.1` (fallback). Automatically absent on any non-local environment.
- **Analytics env** — `Analytics::is_prod_host()` in `basecamp-analytics.php` checks `WP_ENVIRONMENT_TYPE`; GA loads everywhere but only sends config hits when the env is `production` (or unset, which defaults to production).
- **WooCommerce** — guarded by `WooCommerceIntegration::is_active()` inside `woocommerce-functions.php`; safe to load unconditionally.
- **Feature flags** — Cookie consent, Schema output, and WebP optimisation can each be toggled at **Appearance → Theme Settings** without touching code. See `Docs/developer/07-theme-settings.md`.

---

## REST API

A health-check endpoint is registered at `GET /wp-json/basecamp/v1/ping` — returns `{"status":"ok"}`.

Defined in `inc/rest/basecamp-rest-endpoints.php`.