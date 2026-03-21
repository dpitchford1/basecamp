# MI Concept Design — Developer Reference

> **Theme:** MI Concept Design · **Version:** 1.0 · **Author:** Kaneism · **License:** WTFPL  
> **Author URI:** https://kaneism.com · **Text Domain:** `basecamp`

---

## Table of Contents

1. [Development Environment](#1-development-environment)
2. [Repository Structure](#2-repository-structure)
3. [Asset Loading Strategy](#3-asset-loading-strategy)
4. [SCSS Architecture](#4-scss-architecture)
5. [JavaScript Architecture](#5-javascript-architecture)
6. [Theme Bootstrap & PHP Modules](#6-theme-bootstrap--php-modules)
7. [WordPress Domain Modules & CPTs](#7-wordpress-domain-modules--cpts)
8. [Custom SEO Module](#8-custom-seo-module)
9. [WebP Image Optimization](#9-webp-image-optimization)
10. [Template System](#10-template-system)
11. [Header & Footer Architecture](#11-header--footer-architecture)
12. [Naming Conventions & Guardrails](#12-naming-conventions--guardrails)
13. [WordPress Configuration](#13-wordpress-configuration)

---

## 1. Development Environment

### IDE
**Visual Studio Code** — workspace file: `basecamp.code-workspace`

### Required Extensions

| Extension | Purpose |
|---|---|
| **Live Sass Compiler** | Compiles `.scss` → `.css` on save |
| **Auto-Minify** | Minifies compiled CSS/JS to `.min.css` / `.min.js` automatically |

> There is **no webpack, Vite, Grunt, or npm build pipeline.** The extension-based workflow is intentional. Do not introduce one unless explicitly requested.

### Live Sass Compiler — Workspace Config

Settings are stored at `.vscode/settings.json` (project-level, not user-level):

```jsonc
{
  "liveSassCompile.settings.formats": [
    {
      "format": "expanded",
      "extensionName": ".css",
      "savePath": "~/../core"
    },
    {
      "format": "compressed",
      "extensionName": ".min.css",
      "savePath": "~/../build"
    }
  ],
  "liveSassCompile.settings.generateMap": false,
  "liveSassCompile.settings.excludeList": [
    "/**/node_modules/**",
    "/.vscode/**",
    "/**/wp-content/plugins/**",
    "/**/wp-admin/**",
    "/**/wp-content/**"
  ]
}
```

**Output rules:** For any `.scss` entry file in scss, Live Sass outputs:
- Expanded CSS → core
- Compressed/minified CSS → build

Only non-partial files (no `_` prefix) are compiled. `library/scss/` (WP admin styles) is excluded.

### SCSS Version
**Dart Sass** — via Live Sass Compiler. Uses modern `@use`/`@forward` module syntax throughout. Legacy `@import` is not used.

### PHP Linting
After any PHP edit:
```bash
php -l <file>
```

### Local Environment
- Local server: `127.0.0.1` or `::1`
- Development tools activate automatically based on `REMOTE_ADDR` — no manual toggle needed.

---

## 2. Repository Structure

```
/                                        ← WordPress root
├── .vscode/settings.json                ← Live Sass Compiler config (project-level)
├── assets/                              ← Root-level assets (separate from theme — intentional)
│   ├── css/
│   │   ├── build/                       ← Production minified CSS (committed)
│   │   │   ├── critical-css.min.css
│   │   │   ├── mi-base-layout.min.css
│   │   │   └── mi-global-layout.min.css
│   │   ├── core/                        ← Expanded compiled CSS (reference/debug)
│   │   ├── resources/                   ← Third-party CSS (Swiper, video player)
│   │   └── scss/                        ← SCSS source — edit here
│   │       ├── mi-toolbox/              ← Design tokens, mixins, breakpoints
│   │       ├── critical-css/            ← Critical path partials
│   │       ├── mi-base-layout/          ← Structural layout partials
│   │       ├── mi-global-layout/        ← Component partials
│   │       ├── _archive/                ← Deprecated/retired SCSS (do not import)
│   │       ├── critical-css.scss        ← Entry → critical-css.min.css
│   │       ├── mi-base-layout.scss      ← Entry → mi-base-layout.min.css
│   │       └── mi-global-layout.scss    ← Entry → mi-global-layout.min.css
│   ├── js/
│   │   ├── core/base.min.js             ← Main site JS (loaded on every page)
│   │   ├── resources/swiper.min.js      ← Swiper carousel (conditional)
│   │   └── build/                       ← Page-specific JS bundles
│   ├── fonts/                           ← Self-hosted Poppins woff2
│   └── img/                             ← Global images and SVGs
│
├── wp-content/themes/basecamp-design/  ← Active theme — edit here
│   ├── functions.php                    ← Theme entry point
│   ├── style.css                        ← WordPress theme manifest only (no styles)
│   ├── header.php / footer.php
│   ├── inc/                             ← All PHP modules
│   ├── templates/                       ← Reusable layout wrappers
│   ├── template-parts/                  ← Atomic UI components
│   ├── page-templates/                  ← Custom page templates
│   └── library/                         ← WP admin/editor SCSS + compiled CSS
│
└── wp-content/themes/basecamp/         ← Legacy backup — do not reference or edit
```

### Why assets is separate from the theme folder

The root assets directory enables **template-level, fine-grained CSS/JS loading** without routing everything through `wp_enqueue_scripts`. Each CSS file is loaded only on pages that need it, with full control over `media`, `fetchpriority`, and `defer`/`async` attributes — none of which WordPress enqueue handles as precisely. This is a deliberate performance architecture decision.

---

## 3. Asset Loading Strategy

The theme **bypasses `wp_enqueue_*` for all primary frontend assets**, using direct `<link>` and `<script>` tags in `header.php` and `footer.php` instead.

### CSS Loading

| File | Method | Reason |
|---|---|---|
| `critical-css.min.css` | **Inline `<style>`** via PHP (transient-cached) | Zero render-blocking; fastest first paint |
| `mi-base-layout.min.css` | `<link media="screen">` (blocking) | Structural layout must be synchronous |
| `mi-global-layout.min.css` | `<link media="print" onload="this.removeAttribute('media')">` | Non-blocking async CSS load pattern |
| `swiper.min.css` | `<link>` conditional: sector pages only | Page-specific; not loaded globally |
| `video-player.min.css` | `<link>` conditional: `our-reel` page only | Page-specific |

### JS Loading

| File | Method | When |
|---|---|---|
| `base.min.js` | `async` | Every page |
| `swiper.min.js` | `defer`, conditional | `is_singular('sector')` or sectors template |
| `video.min.js` + `video-reel.min.js` | `defer`, conditional | `is_page('our-reel')` |

### Critical CSS Caching

`Basecamp_Frontend::output_critical_css()` uses two transients per file:
- **`basecamp_critical_css`** — the minified CSS string (TTL: 1 day)
- **`basecamp_critical_css_mtime`** — file's `filemtime()` for cache invalidation

Cache invalidates automatically when the source file changes. Minification is whitespace collapse only (`preg_replace('/\s+/', ' ', $css)`).

---

## 4. SCSS Architecture

### Overview

The SCSS system is divided into four layers. All source lives in scss.

```
scss/
├── mi-toolbox/          ← Design system foundation (tokens, mixins, breakpoints)
├── critical-css/        ← Critical path CSS → critical-css.min.css
├── mi-base-layout/      ← Structural CSS → mi-base-layout.min.css
├── mi-global-layout/    ← Component CSS → mi-global-layout.min.css
└── _archive/            ← Retired partials (do not import)
```

### Module System

All files use `@use`/`@forward`. `@import` is not used anywhere.

**Rule:** Every file that needs design tokens starts with:
```scss
@use 'mi-toolbox' as *;
```
Never import individual toolbox files directly — always go through the barrel.

---

### Layer 1: `mi-toolbox/` — Design System Foundation

The shared foundation for all SCSS. Always loaded first.

| File | Role |
|---|---|
| _index.scss | Public barrel — `@forward` all toolbox partials |
| `_theme.scss` | All design tokens: color swatches, font stacks, `$themes` map, `$breakpoints` maps |
| _variables.scss | `$themes`/`$text-settings` stubs (consumed internally) |
| _mixins-master.scss | All mixins and functions |

**_index.scss (barrel):**
```scss
@forward 'variables' hide $themes, $text-settings;
@forward 'mixins-master';
@forward 'theme';
```

#### Design Tokens (`_theme.scss`)

**Brand colours:**
| Token | Value | Usage |
|---|---|---|
| `$swatch-primary` | `#00a5b5` | Teal / Brand Blue |
| `$swatch-secondary` | `#f58226` | Orange |
| `$swatch-accent--light` | `#56005b` | Purple |
| `$swatch-accent--dark` | `#b0aeff` | Lavender |
| `$bling--dark` | `#01f3d8` | Cyan highlight |

**Breakpoints (`$breakpoints` map):**

| Key | em value | px equivalent |
|---|---|---|
| `600` | `37.5em` | 600px |
| `768` | `48em` | 768px |
| `920` | `57.5em` | 920px |
| `1024` | `64em` | 1024px |
| `1280` | `80em` | 1280px |
| `1440` | `90em` | 1440px |
| `1600` | `100em` | 1600px |
| `2100` | `131.25em` | 2100px |

`$breakpoints-max` contains a single key: `767` → `47.938em` (used for `max-width` only).

Keys are **px values as integers** — no abstract names like `sm`/`md`/`lg`. This eliminates ambiguity.

#### Mixins & Functions (_mixins-master.scss)

| Mixin / Function | Signature | Description |
|---|---|---|
| `clearfix()` | `@mixin` | Table-based clearfix |
| `clearfix-after()` | `@mixin` | `clear: both` |
| `theme()` | `@mixin` | Iterates `$themes`; scopes `@content` under `.light &` / `.dark &` |
| `theme-get($key)` | `@function` | Reads token from current `$theme-map` |
| `text-scale($level)` | `@function` | Returns `font-size` from `$text-settings` |
| `line-height($level)` | `@function` | Returns `line-height` from `$text-settings` |
| `text-setting($level)` | `@mixin` | Applies `font-size` + `line-height` |
| `respond-to($bp)` | `@mixin` | `min-width` media query — key must exist in `$breakpoints` |
| `respond-below($bp)` | `@mixin` | `max-width` media query — key must exist in `$breakpoints-max` |

**Build enforcement:** Passing an unknown key to `respond-to()` or `respond-below()` throws a Sass `@error` and breaks the build immediately.

---

### Layer 2–4: Entry Files & Partials

Each bundle follows the same pattern:

```scss
// 1. Load toolbox
@use 'mi-toolbox' as *;

// 2. Load partials (base styles output automatically via @use)
@use "layer/partial-name" as *;

// 3. Load responsive aggregator
@use "layer/responsive";

// 4. Output all consolidated media queries
@include responsive.all();
```

**`mi-base-layout` partials:**
| File | Role |
|---|---|
| `_global-structure.scss` | `.fluid`, `.region`, `.content-wrapper` |
| _mi-header.scss | Header barrel (`@forward` header sub-partials) |
| `_global-footer.scss` | Footer grid and layout |
| `_grid-global.scss` | CSS Grid system, `.grid-general`, column variants |
| `_responsive.scss` | Breakpoint orchestration for this bundle |
| `header/_global-header.scss` | Header component styles |
| `header/_menu-enhanced.scss` | Off-canvas navigation (also imported by critical-css) |
| `header/_menu-global.scss` | Desktop navigation menu |
| `header/_menu-small-screen.scss` | Mobile menu trigger/icons |

**`mi-global-layout` components:**
| File | Role |
|---|---|
| `_utilities.scss` | Display, flex, grid utility classes |
| `_responsive.scss` | Breakpoint orchestration for this bundle |
| `components/_heros.scss` | Hero section variants |
| `components/_featured-projects.scss` | Featured project cards |
| `components/_carousels.scss` | Swiper carousel layout |
| `components/_cards.scss` | Card components |
| `components/_content-menu.scss` | In-page content navigation, entry content |
| `components/_forms.scss` | Form normalization + CF7 structure |
| `components/_buttons.scss` | Button variants |
| `components/_video-player.scss` | YouTube lite embed styles |
| `components/_icons.scss` | Icon display helpers |

**`critical-css` partials:**
| File | Role |
|---|---|
| `_website-setup.scss` | `@font-face`, `:where(:root)` normalize, base resets |
| `_typography.scss` | Headings, links, body text |
| `_media.scss` | Images, figures, embeds |
| `_interactive.scss` | Focus states, interactive elements |
| `_lists.scss` | List and table normalization |
| `_responsive.scss` | Critical breakpoint rules |

> `_menu-enhanced.scss` is imported directly into `critical-css.scss` (bypassing the `mi-base-layout` barrel) — the off-canvas animation must be in the critical path to prevent flash of unstyled header. This is intentional.

---

### Responsive Mixin Workflow

Adding responsive styles to an existing component:

1. **Open the component partial** (e.g. `_heros.scss`)
2. **Write CSS inside the relevant `bp-*` mixin:**
   ```scss
   @mixin bp-1024() {
     .hero--heading { font-size: clamp(2rem, 4vw, 6rem); }
   }
   ```
3. **If this is a new breakpoint for this component**, add the `@include` to the bundle's `_responsive.scss`:
   ```scss
   @include respond-to(1024) {
     @include heros.bp-1024();
   }
   ```

The compiled output will have exactly **one `@media (min-width: ...)` block per breakpoint** per CSS file, regardless of how many components contribute rules to it.

**Never** write raw `@media` blocks directly in a partial — always use the `bp-*()` mixin pattern.

---

## 5. JavaScript Architecture

```
assets/js/
├── core/
│   ├── base.js           ← Source
│   └── base.min.js       ← Minified (loaded on every page, async)
├── resources/
│   ├── navigation.js     ← Currently inactive
│   └── swiper.min.js     ← Swiper carousel (conditional, defer)
└── build/
    ├── video.min.js       ← Plyr video player (our-reel page only)
    └── video-reel.min.js  ← Reel-specific JS (our-reel page only)
```

- Wrap in IIFE or module pattern. Use `basecampApp` as the single global if needed.
- Vanilla JS preferred on frontend. jQuery is acceptable in admin only.
- Data passed via `wp_localize_script` or `wp_add_inline_script` — no inline globals.

---

## 6. Theme Bootstrap & PHP Modules

**Entry point:** functions.php

All modules are composed via `require_once` in `functions.php`. Hook-first design: behavior is added in module files using `add_action`/`add_filter`, not inline template logic.

### Load Order

```
functions.php
├── inc/class-basecamp.php                         ← Core: menus, image sizes, theme supports
├── inc/frontend/class-basecamp-svg-icons.php      ← Social SVG icon system
├── inc/frontend/class-basecamp-frontend.php       ← Frontend helpers, critical CSS, output buffer
├── inc/frontend/remove-bloat.php                   ← Cleanup: emoji, jQuery migrate, CF7 conditional
├── inc/theme-functions/basecamp-content-sectors.php
├── inc/theme-functions/basecamp-content-news.php
├── inc/theme-functions/basecamp-content-services.php
├── inc/theme-functions/basecamp-analytics.php     ← GA4 (production-gated)
├── inc/theme-functions/basecamp-our-story.php
├── inc/theme-functions/basecamp-home.php
├── inc/theme-functions/basecamp-content-video.php ← file_exists() guard
├── inc/admin/ (all files)                          ← is_admin() gate
├── inc/img-optimization/ (all files)               ← WebP subsystem
├── inc/seo/class-basecamp-seo.php                 ← SEO (title, meta, OG)
├── inc/rest/basecamp-rest-endpoints.php           ← REST: GET /wp-json/test/v1/ping
├── inc/core/basecamp-scheduled-events.php
└── inc/development/class-basecamp-development.php ← REMOTE_ADDR gate (local only)
```

### Key Registered Theme Supports

| Support | Value |
|---|---|
| `title-tag` | ✅ |
| `post-thumbnails` | `post`, `page`, `sector` |
| `html5` | search-form, gallery, caption, widgets, style, script |
| `widgets-block-editor` | **Removed** |

### Custom Image Sizes

| Handle | Width | Height | Crop |
|---|---|---|---|
| `basecamp-img-xxl` | 1660 | 934 | No |
| `basecamp-img-xl` | 1440 | 810 | No |
| `basecamp-img-lg` | 1280 | 720 | No |
| `basecamp-img-m` | 980 | 551 | No |
| `basecamp-img-sm` | 600 | 338 | No |
| `basecamp-img-s` | 400 | 225 | No |
| `portait-sm` | 300 | 400 | Yes (3:4) |
| `portait-m` | 640 | 853 | Yes (3:4) |
| `portait-lg` | 960 | 1280 | Yes (3:4) |

### Menu Locations

| Slug | Label |
|---|---|
| `primary` | Primary Menu |
| `utility` | Secondary Menu |
| `footer` | Footer Menu |
| `social` | Social Menu |

### Performance & Bloat Removal (`remove-bloat.php`)

Removed from `wp_head`: feed links, RSD, oEmbed, REST API link, WP generator tag, global styles, block library CSS, jQuery migrate.

CF7 assets are globally disabled and re-enqueued only when the current page slug is `contact` or post content contains a CF7 shortcode.

---

## 7. WordPress Domain Modules & CPTs

### Sectors CPT

**File:** `inc/theme-functions/basecamp-content-sectors.php`

| Property | Value |
|---|---|
| Post type slug | `sector` |
| Archive slug | `/sectors/` |
| `hierarchical` | `true` |
| `show_in_rest` | `false` |
| Supported features | title, editor, author, thumbnail, excerpt, custom-fields, page-attributes |

Includes sector gallery metabox and featured project helpers. Active menu state on single sector pages is enforced via `Basecamp_Frontend::menu_selected_class()`.

### Services Metabox

**File:** `inc/theme-functions/basecamp-content-services.php`

| Constant | Value |
|---|---|
| `BASECAMP_SERVICES_META_KEY` | `_basecamp_services` |
| `BASECAMP_SERVICES_MAX` | `6` |

Metabox appears only on pages using `page-services.php` template or slug `services`. Data stored as a serialised array of up to 6 items. Nonce-protected.

### News Helpers

**File:** `inc/theme-functions/basecamp-content-news.php`

| Function | Description |
|---|---|
| `basecamp_get_latest_news($args)` | WP_Query wrapper; returns `WP_Post[]`. Defaults: 3 posts. |
| `basecamp_build_news_view_model($post, $index, $size, $words)` | Builds structured view-model array for a single post (thumbnail, alt, excerpt). |

### Analytics — GA4

**File:** `inc/theme-functions/basecamp-analytics.php`

GA snippet is output inline in `wp_head`. On non-production hosts, the snippet is present but tracking is suppressed. Change the GA Measurement ID via the `BASECAMP_GA_MEASUREMENT_ID` constant — not in templates.

### WebP Subsystem

**Directory:** `inc/img-optimization/`

When modifying WebP code, verify **both** the admin edit/save path and frontend rendering output.

---

## 8. Custom SEO Module

**Directory:** `inc/seo/`  
**Loader:** `inc/seo/class-basecamp-seo.php` — requires the three sub-modules in order.

**Plugin deference:** Both the meta description and social meta classes check for Yoast SEO (`WPSEO_Frontend`) and RankMath at `init`. If either is active, the module bails entirely — no hooks are registered and no duplicate tags are output.

---

### 8.1 Title System

**File:** `inc/seo/basecamp-title-functions.php`

Uses an **extensible class chain** — each extension class is tried in order; the first non-`null` result wins. Falls back to `Basecamp_Title_Core`.

#### Extension Resolution Order

| Class | Context | Title Format |
|---|---|---|
| `Basecamp_Title_Sector` | Sector CPT (checked first) | Single: `PostTitle - Sectors - SiteName` |
| | Sector archive | `Sectors - SiteName` |
| `Basecamp_Title_Work` | `work` post type | `PostTitle - Work - SiteName` |
| | `work` taxonomy | `TermName - Work - SiteName` |
| | Work archive | `Work - SiteName` |
| `Basecamp_Title_Woo` | WooCommerce product | `PostTitle - Shop - SiteName` |
| | Product category/tag | `TermName - Shop - SiteName` |
| | Shop page | `Shop - SiteName` |
| `Basecamp_Title_Core` | All other contexts (fallback) | `PostTitle - SiteName` or site name only |

#### Adding a New Post Type Title

Create a new class following the same pattern, then add it to `Basecamp_Title_Manager::$extensions`:

```php
class Basecamp_Title_MyType {
    public static function maybe_title( $title ) {
        if ( is_singular('my-type') ) {
            return get_the_title() . ' - My Type - ' . get_bloginfo('name');
        }
        return null; // pass to next extension
    }
}
```

#### Hooks

| Hook | Priority | Callback |
|---|---|---|
| `pre_get_document_title` | 1 | `Basecamp_Title_Manager::filter_title` |
| `wp_title` | 1 | `Basecamp_Title_Manager::filter_wp_title` |

> Priority 1 gives the theme full control of the `<title>` tag before WordPress's own title assembly runs.

---

### 8.2 Meta Description

**File:** `inc/seo/basecamp-meta-description-functions.php`

Outputs `<meta name="description">`, `<meta property="og:description">`, and `<meta name="twitter:description">` at `wp_head` priority 1.

#### Page-Type Resolution

| Condition | Description Source |
|---|---|
| Sector archive | Page with slug `sector` (excerpt → content → top 5 sector names fallback) |
| Singular | Post excerpt → first 30 words of `post_content` |
| Category / tag / taxonomy | `$term->description` → `"Browse all {term} content"` |
| Post type archive (generic) | `get_post_type_object()->description` |
| Author archive | `"Posts by {display_name}"` |
| Search results | `"Search results for \"{query}\""` |
| Date archive | `"Archive for {date}"` |
| Default / fallback | `get_bloginfo('description')` (site tagline) |

All descriptions are trimmed to 30 words. Wrapped in try/catch — on exception falls back to site tagline and logs to `error_log`.

#### Exposed Filters

| Filter | Purpose |
|---|---|
| `basecamp_sector_archive_page_slug` | Override the static page slug used for sector archive description (default: `'sector'`) |
| `basecamp_sector_archive_meta_description` | Override the final sector archive description string |

#### Utility Method

`Basecamp_Meta_Description::get_meta_description( $object, $word_count )` — Returns a description string for any `WP_Post`, post ID, or `WP_Term` without outputting. Available for use in other theme code.

---

### 8.3 Social Meta (Open Graph & Twitter Card)

**File:** `inc/seo/basecamp-social-meta-functions.php`

Outputs OG and Twitter Card tags at `wp_head` priority 2 (after meta description at priority 1). Also removes any legacy `add_opengraph` functions from `wp_head` on init.

#### OG Tags Output

`og:title`, `og:url`, `og:type`, `og:site_name`, `og:image` — plus `og:image:width` + `og:image:height` for local images (resolved via `getimagesize()`).

#### Twitter Card Tags Output

Always `summary_large_image`. Outputs `twitter:site` if the `basecamp_twitter_site` theme mod is set. Outputs `twitter:creator` from author profile on singular pages.

#### Per-Context Values

| Context | Type | Image Source |
|---|---|---|
| Singular (post/page/CPT) | `article` (or `product` for WooCommerce) | Featured image (full size) |
| Home / front page | `website` | `basecamp_home_share_image` theme mod |
| Taxonomy / category / tag | `website` | `thumbnail_id` term meta |
| Author archive | `website` | `get_avatar_url()` at 512px |
| 404 / search / archive | `website` | Default share image |

#### Theme Mods

| Key | Default | Purpose |
|---|---|---|
| `basecamp_default_share_image` | `/assets/img/logos/login_logo.png` | Fallback image for all contexts |
| `basecamp_home_share_image` | Falls back to default | Homepage-specific OG image |
| `basecamp_twitter_site` | `''` | `@handle` for `twitter:site` tag |

#### Utility Method

`Basecamp_Social_Meta::get_social_meta( $post_id )` — Returns an array of `title`, `url`, `image`, `type`, `description`, `site_name` for a given post. Available for use in other theme code.

---

## 9. WebP Image Optimization

**Directory:** `inc/img-optimization/`

The WebP subsystem handles automatic conversion on upload, frontend URL rewriting for supported browsers, a bulk conversion admin tool, and a developer testing page.

> **Critical:** Admin save safety is enforced at multiple layers. Never modify this subsystem without testing both the admin upload/edit path and the frontend rendering path.

---

### 9.1 Frontend Serving (`basecamp-webp-functions.php`)

Browser support is detected via `HTTP_ACCEPT` header (`image/webp`) with a UA string fallback. WebP is served only to supported browsers.

#### URL Rewriting

`basecamp_get_webp_image( $url )` checks for two file formats in order:

1. **Replacement format:** `image.jpg` → `image.webp`
2. **Appended format:** `image.jpg` → `image.jpg.webp` *(upload hook produces this format)*

Returns the WebP URL if the file exists on disk, `false` otherwise.

#### Active Filters

| Filter | Priority | What It Rewrites |
|---|---|---|
| `wp_get_attachment_image_src` | 10 | Image `src` URL |
| `wp_get_attachment_image_attributes` | 10 | `src` and full `srcset` string |
| `post_thumbnail_html` | 10 | `src` in rendered `<img>` HTML |
| `wp_get_attachment_image` | 999 | Final pass on full `<img>` HTML |
| `the_content` | 10 | All `<img>` tags in post content |
| `the_content` | 999 | `<img>` tags with `wp-image-*` class specifically |

#### Exclusion Rules

WebP rewriting is skipped when:

- Browser doesn't support WebP
- Admin context (non-AJAX) on `post.php` or `post-new.php`
- `?disable_webp` or `?test_native_srcset` query params are present
- Post contains a `[gallery]` shortcode
- Image element has class `native-img` or `no-webp`

---

### 9.2 Automatic Conversion on Upload (`basecamp-webp-conversion.php`)

WebP files are generated automatically when images are uploaded to the Media Library.

`wp_generate_attachment_metadata` hook fires after WordPress generates image sizes. For each upload:

1. Converts the **original full-size** file → `original.jpg.webp`
2. Iterates `$metadata['sizes']` and converts **every registered thumbnail size**

The same admin save guard applies — conversion is skipped on `post.php`/`post-new.php`/`edit.php` edit/update actions to prevent unintended re-conversion on post save.

#### Conversion Method Cascade

`basecamp_convert_to_webp()` tries methods in this order:

1. **PHP GD** (`imagewebp`) — PNG transparency preserved via `imagealphablending`/`imagesavealpha`
2. **PHP Imagick** — `webp:method=6`, `webp:low-memory=true`
3. **`cwebp` CLI** — external binary if available

**Quality:** Default `80`. Configurable via the bulk conversion admin UI.

**Size guard:** If the resulting WebP file is *larger* than the original, it is deleted. WebP files are only kept when they reduce file size.

**Memory guard:** Before conversion, memory requirements are estimated (`width × height × channels × bits / 8 × 3`). If this exceeds 80% of PHP's memory limit, the image is resized to `2000×2000` max in a temp file before conversion.

---

### 9.3 Bulk Conversion Admin Tool

**Location:** WP Admin → **Tools → WebP Conversion**

Converts the entire Media Library to WebP in batches via AJAX — one image at a time to avoid timeouts.

#### Workflow

1. Set quality (0–100 slider, default 80) and click **Start**
2. JS polls `wp_ajax_basecamp_webp_process_single` — one image per request
3. Progress bar and live log update in real time
4. Pause / Resume supported mid-batch
5. **Reset** cancels the current batch and marks all records back to `pending`

#### Database Tables

Two custom tables are created lazily on `init`:

**`{prefix}basecamp_webp_conversions`** — One row per image file:

| Column | Purpose |
|---|---|
| `attachment_id` | Media library attachment ID |
| `file_path` | Absolute path to original file |
| `file_size` / `webp_size` | Before/after sizes in bytes |
| `status` | `pending` / `processing` / `completed` / `error` |
| `attempts` | Retry counter — max 3 before a record is skipped |
| `error_message` | Failure detail |
| `conversion_time` | Seconds taken |

**`{prefix}basecamp_webp_stats`** — One row per batch run:

| Column | Purpose |
|---|---|
| `batch_id` | MD5 unique batch identifier |
| `total_images` / `processed_images` | Batch progress counters |
| `successful_conversions` / `failed_conversions` | Outcome counts |
| `total_original_size` / `total_webp_size` | Aggregate bytes for space-saved calculation |
| `quality` | Quality setting used for this batch |
| `status` | `running` / `completed` / `cancelled` |

#### AJAX Endpoints

| Action | Capability | Purpose |
|---|---|---|
| `basecamp_webp_get_status` | `manage_options` | Returns current batch progress as JSON |
| `basecamp_webp_process_single` | `manage_options` | Converts one image and returns result |

Both endpoints require the `basecamp_webp_ajax_nonce` nonce.

---

### 9.4 WebP Testing Tool

**Location:** WP Admin → **Tools → WebP Testing**

Developer read-only tool — no configuration, no side effects. Displays three panels:

1. **Browser Support** — raw `HTTP_ACCEPT` header, UA string, and support detection result
2. **File Path Testing** — for the 5 most recent uploads: original URL, detected WebP URL, which file formats exist on disk, and which path is actively being used
3. **Filter Audit** — lists all callbacks on `wp_get_attachment_image_src`, `wp_get_attachment_image_attributes`, and `post_thumbnail_html` with their priorities and class names

---

## 10. Template System

### Root Templates
`index.php`, `page.php`, `single.php`, `archive.php`, `404.php`, `single-sector.php`, `archive-sector.php`, `page-home.php`, `page-contact-us.php`, `page-our-story.php`, `page-services.php`, `page-html.php`, `page-video.php`

### Page Templates (`page-templates/`)
`sectors.php`, `sector-featured-project.php`, `video-reel.php`

### Templates (`templates/`)
Reusable layout wrappers: `archive-loop.php`, `byline.php`, `category-tags.php`, `content-excerpt.php`, `header-title.php`, `loop.php`, `single-loop.php`, `post-navigation.php`

### Template Parts (`template-parts/`)

| Path | Purpose |
|---|---|
| `news/latest-news.php` | Latest news cards renderer |
| `sector/featured-projects.php` | Sector-level featured projects |
| `featured-project.php` | Single featured project card |
| `featured-projects-global.php` | Global featured projects section |
| `video-carousel.php` | Video carousel component |
| `development-pilot.php` | DevPilot local dev bar |

**Rule:** Template parts display only — no queries inside unless a trivial fallback. Queries belong in `inc/theme-functions/`.

---

## 11. Header & Footer Architecture

### `header.php` — Load Sequence

1. `Content-Type` + Viewport meta
2. `<link rel="preload">` — MI logo SVG
3. Service worker registration (inline script)
4. JS class swap: `no-js` → `has-js enhanced` (inline script)
5. **Critical CSS** — `Basecamp_Frontend::output_critical_css()` → inline `<style id="critical-css">`
6. `mi-base-layout.min.css` — blocking `<link>`
7. `mi-global-layout.min.css` — deferred `<link media="print" onload="...">`
8. Conditional: `swiper.min.css` (sector pages), `video-player.min.css` (our-reel)
9. Favicons + Apple meta tags
10. `wp_head()` hook

### `footer.php` — Load Sequence

1. Footer grid: logo, nav, contact (schema.org `LocalBusiness`), social icons, copyright
2. `base.min.js` — `async` (always)
3. `swiper.min.js` — `defer`, conditional
4. `video.min.js` + `video-reel.min.js` — `defer`, conditional
5. `wp_footer()` hook

### Schema.org
`LocalBusiness` schema is output in the footer: name, email, telephone, URL, opening hours, geo coordinates, postal address.

---

## 12. Naming Conventions & Guardrails

### PHP Naming

| Prefix | Scope |
|---|---|
| `basecamp_` | Functions, meta keys, hooks specific to this project |
| `basecamp_` | Shared theme subsystems |
| `Basecamp_` | Class names (PascalCase) |
| `_basecamp_` | Meta keys (leading underscore = hidden from custom fields UI) |

See [Docs/CODESTYLE.md](Docs/CODESTYLE.md) for full PHP coding standards, escaping rules, hooks strategy, and PR checklist.

### SCSS / CSS Naming

| Pattern | Meaning |
|---|---|
| `is--*` | State: `is--home`, `is--sticky`, `is--contact` |
| `has--*` | Modifier: `has--breadcrumb`, `has--padding` |
| `type-*` | Post type: `type-sector`, `type-post` |
| `inner--content` | Inner page layout modifier |
| `menu--selected` | Active menu item |
| `fluid` | Full-width fluid container |
| `region` | Layout region wrapper |
| `ra` | Responsive-auto grid modifier |
| `cf` | Clearfix |
| `hide-text` | Visually hidden, accessible text |

### Agent Guardrails

- **Do not** modify `wp-admin/`, `wp-includes/`, or root `wp-*.php` unless explicitly requested.
- **Do not** modify the root `assets/` folder unless the task specifically requires it.
- **Do not** use `wp-content/themes/basecamp/` (legacy) as a reference.
- **Do not** introduce Tailwind, Bootstrap, or any third-party CSS framework.
- **Do not** add a build pipeline (webpack/Vite/Grunt/npm).
- **Do** add new theme behavior under `inc/` and wire it in `functions.php`.
- **Do** keep edits small and localized; preserve existing hook priorities.
- **Do** verify both admin save and frontend render paths when touching WebP or metabox code.

---

## 13. WordPress Configuration

| Setting | Value |
|---|---|
| `WP_DEBUG` | `false` (lines commented out in `wp-config.php`) |
| `WP_POST_REVISIONS` | `0` (disabled) |
| `DISABLE_WP_CRON` | `true` — cron must be triggered by system/server cron |
| `WP_MEMORY_LIMIT` | `1024` MB |
| `FS_METHOD` | `direct` |
| `WP_ALLOW_REPAIR` | `true` |

> Avoid adding extra debug logging. If needed, wrap in `if ( WP_DEBUG )` and use the `basecamp_log()` helper (see [Docs/CODESTYLE.md](Docs/CODESTYLE.md) §21).

---

*Evolves with the project. Update the version in `style.css` when making significant architectural changes.*
