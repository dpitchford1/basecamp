# Environment Setup

Steps to get a working local or staging environment from the repository. Items marked **⚠️ gitignored** are not in the repo and must be sourced separately.

> **This document is a work in progress.** Additional detail — particularly around database, hosting config, and plugin sources — to be added by the project owner.

---

## Requirements

| Requirement | Minimum | Production |
|---|---|---|
| WordPress | 6.0 | Latest stable |
| PHP | 7.4 | 8.5 |
| MySQL | 8.0 | 8.0 |
| MariaDB | 10.5 | — |

**PHP extensions required:**
- **GD** or **ImageMagick** with WebP support — needed for on-upload WebP conversion
- Without either, WebP conversion silently skips (uploads still work, just no `.webp` files generated)

**Local server:** MAMP, Valet, Laravel Herd, or any stack that meets the above.

---

## Theme at a Glance

For a full feature overview, open the **Docs** panel inside wp-admin (Dashboard → Basecamp Docs). Key points relevant to setup:

- **No jQuery dependency** — theme JS is vanilla. jQuery is still enqueued by WordPress core but the theme does not rely on it.
- **No build pipeline** — SCSS is compiled by the Live Sass Compiler VS Code extension on save. No npm, webpack, or grunt.
- **Schema.org markup** — `Organization` structured data is output in the footer. No plugin required.
- **Text domain:** `basecamp` (all `__()` / `_e()` calls throughout the theme)

---

## What Is and Isn't in the Repo

### In the repo
- Full theme: `wp-content/themes/basecamp/`
- Root `assets/` directory (CSS, JS, images)
- Any other non-gitignored plugin directories

### ⚠️ Not in the repo — must be sourced/installed manually

| What | Notes |
|---|---|
| `wp-config.php` | Create from `wp-config-sample.php` |
| `wp-content/uploads/` | Pull from production or staging |
| `assets/css/build/` | Regenerate via Live Sass Compiler (open any `.scss` file and save) |
| `assets/fonts/` | Source from project assets |
| `assets/img/` | Source from project assets |
| Plugin: **Classic Editor** | Install from wp-admin → Plugins |
| Plugin: **WP Sweep** | Install from wp-admin → Plugins (dev utility) |

---

## Setup Steps

### 1. Clone the repo
```bash
git clone <repo-url> basecamp
cd basecamp
```

### 2. Create `wp-config.php`
Copy `wp-config-sample.php` to `wp-config.php` and configure:
- Database credentials
- `WP_DEBUG true` for local dev
- `WP_HOME` / `WP_SITEURL` if using a custom local domain

### 3. Import the database
```bash
# Example with WP-CLI
wp db import dump.sql
wp search-replace 'https://production-url.com' 'http://local.test'
```

### 4. Install plugins
Install **Classic Editor** via wp-admin → Plugins → Add New, or via WP-CLI:
```bash
wp plugin install classic-editor --activate
```

### 5. Regenerate compiled CSS
Open any `.scss` file in `assets/css/scss/` and save — the Live Sass Compiler VS Code extension will compile all watched files to `assets/css/build/`.

If Live Sass Compiler is not yet configured, install it from the VS Code Extensions panel (`ritwickdey.live-sass`).

### 6. Regenerate image sizes (if needed)
If the registered image sizes in `inc/class-basecamp.php` have changed since the database was exported:
```bash
wp media regenerate --yes
```

### 7. Verify WebP conversion is working
- Go to **Tools → WebP Conversion** in wp-admin
- The page should confirm GD or ImageMagick WebP support is available
- Upload a test image and check `/wp-content/uploads/` for a `.webp` file alongside it

---

## VS Code Extensions Required

| Extension | ID | Purpose |
|---|---|---|
| Live Sass Compiler | `glenn2223.live-sass` | Compiles `.scss` → `assets/css/build/` |
| Auto-Minify | — | Minifies `.js` → `.min.js` on save |

---

## Built-in Developer Tools

Both tools activate automatically in a local environment — no configuration needed.

---

### DevPilot Overlay

**File:** `inc/development/class-basecamp-development.php`  
**Trigger:** `REMOTE_ADDR` is `127.0.0.1` or `::1`

A collapsible drawer injected into `wp_footer` on every frontend page load. Automatically absent on any non-localhost IP — nothing to toggle or disable.

**What it shows:**

| Panel | Info |
|---|---|
| Page Details | Live window width in px (updates on resize) |
| Template Information | Current template file, template context, template source/type/path (via `get_current_template()` / `get_template_context()`) |
| Post Type | Shown on singular posts/pages |
| Taxonomy | Term name and taxonomy slug on archive pages |
| HTML Outline Analysis | Button that triggers a full heading hierarchy analysis of the current page — useful for catching heading order issues |

**Assets:** CSS and JS are loaded from `assets/development/` (gitignored — must be present locally). The drawer has its own scoped stylesheet (`devpilot-drawer.min.css`) and an HTML outline analyser (`html-outline.min.js`).

---

### Layout Tester

**File:** `layout-tester.php` (repo root)  
**Companion plugin:** `wp-content/plugins/layout-tester` (gitignored — install locally)  
**URL:** `/layout-tester/`

An unbranded standalone page that renders a series of iframes loading any URL on the site at real device dimensions.

**Setup (one-time per environment):**
1. Install and activate the companion plugin (`wp-content/plugins/layout-tester`)
2. Go to **Pages → Add New** in wp-admin and create a page with the slug `layout-tester` — title and content don't matter, it just needs to exist so WordPress resolves the URL
3. Navigate to `/layout-tester/` — the plugin intercepts the request and serves the tester instead of the normal page template

**Iframe sizes loaded:**

| Category | Widths |
|---|---|
| Phone portrait | 320, 360, 375, 414 px |
| Tablet | 600, 768, 1024 px |
| Desktop | 1280, 1440, 1920 px |

A form at the top accepts any URL on the site — submit it and all iframes reload with the new URL. Defaults to the site root on first load.

> The layout tester bypasses WordPress entirely (no `wp-blog-header.php` load) so it is fast and has no admin overhead. It is purely a front-end visual check tool.

---

## Notes

- `WP_DEBUG` is controlled by the `BASECAMP_ENV` environment variable — set `BASECAMP_ENV=local` for full debug output locally. Never set `BASECAMP_ENV=local` on production.
- GA4 fires on all environments but only sends config hits when `BASECAMP_ENV` is `production` (or unset). Configure the GA4 Measurement ID at **Appearance → Theme Settings** — no code change needed.
