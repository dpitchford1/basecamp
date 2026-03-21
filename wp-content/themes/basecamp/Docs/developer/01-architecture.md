# Theme Architecture

Basecamp is a custom WordPress theme built for performance, clarity, and control. It is purpose-built — not a general-purpose starter — but structured so developers can follow and extend it without untangling a bloated framework.

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
  header.php / footer.php    Global layout wrappers
  page-*.php                 Page-specific templates
  single.php / archive.php   Standard WP templates

  inc/
    class-basecamp.php       Core setup: menus, image sizes, theme supports
    admin/                   Admin area: login styles, dashboard tweaks, docs
    core/                    Scheduled events
    development/             Dev-only tools (local IP gated)
    frontend/                SVG icons, frontend class, cookie consent
    img-optimization/        WebP conversion (GD, Imagick, cwebp)
    rest/                    REST API endpoints
    seo/                     Title, meta description, social meta, schema
    theme-functions/         Analytics, blog rename, custom helpers

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

1. Core theme class (`inc/class-basecamp.php`)
2. Frontend classes (SVG icons, frontend helpers, remove-bloat, cookie consent)
3. Admin-only modules (wrapped in `is_admin()`)
4. SEO modules (`class-basecamp-seo.php` bootstraps title, meta, social, schema)
5. Theme functions (meta link list, analytics)
6. Image optimization (WebP)
7. REST endpoints
8. Scheduled events (cron)
9. Development tools (localhost IP gate)
10. Ecommerce (disabled by default — uncomment `require_once` lines to activate)

---

## Hooks Strategy

- Registration hooks (`init`) stay in dedicated files — no anonymous closures for public APIs
- Filters always have a `@return` docblock
- Hook priorities follow this convention:
  - Priority 4: Cookie consent defaults (before GA at 5)
  - Priority 5: Google Analytics snippet
  - Priority 10: Default (most theme hooks)

---

## Environment Detection

- **Production hosts** defined in `BASECAMP_GA_PROD_HOSTS` constant (set in `basecamp-analytics.php`)
- GA only fires on production hosts
- Dev tools load only when `REMOTE_ADDR` is `127.0.0.1` or `::1`
- `WP_DEBUG` is enabled; avoid extra `error_log()` calls

---

## REST API

A health-check endpoint is registered at `GET /wp-json/basecamp/v1/ping` — returns `{"status":"ok"}`.

Defined in `inc/rest/basecamp-rest-endpoints.php`.

---

