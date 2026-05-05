# Basecamp

A performance-focused, modular WordPress **parent theme** built for developer-controlled multi-project deployments.

Basecamp provides the full PHP module system, no-plugin SEO, automatic WebP conversion, responsive image helpers, and a Dart Sass CSS pipeline. Each project runs as a **child theme** that inherits all of Basecamp's infrastructure and adds only its own templates, assets, and feature modules — keeping the parent untouched and upgradeable.

---

## Requirements

- WordPress 6.4+
- PHP 7.4+ (8.x recommended)
- [Live Sass Compiler](https://marketplace.visualstudio.com/items?itemName=glenn2223.live-sass) (VS Code) — or any Dart Sass compiler

---

## Install

```bash
# In your WordPress themes directory
git clone https://github.com/your-org/basecamp.git
```

Basecamp is a **parent theme** — activate a child theme, not Basecamp directly. See [Child Theme Usage](#child-theme-usage) below.

Or download as a ZIP and upload via **Appearance → Themes → Add New → Upload Theme**.

---

## Environment Setup

Copy `wp-config-sample.php` to `wp-config.php` and add:

```php
define( 'WP_ENVIRONMENT_TYPE', 'local' ); // local | staging | production
define( 'WP_DEBUG', true );
```

`WP_ENVIRONMENT_TYPE` controls DevPilot visibility and analytics behaviour. On production, omit the constant or set it to `'production'`.

---

## Module System

Every feature is a self-contained file loaded via `require_once` in `functions.php`. Enable or disable any module by commenting its load line in and out — nothing else needs to change.

```php
// functions.php — example toggles
require_once __DIR__ . '/inc/frontend/class-basecamp-frontend.php'; // always on
// require_once __DIR__ . '/inc/woocommerce/woocommerce-functions.php'; // uncomment when WooCommerce is active
// require_once __DIR__ . '/inc/theme-functions/basecamp-cpt-scaffold.php'; // uncomment when using custom post types
```

Modules are grouped in `functions.php` by area:

| Group | Path |
|---|---|
| Core setup | `inc/class-basecamp.php` |
| Frontend helpers | `inc/frontend/` |
| Admin customisations | `inc/admin/` |
| SEO (titles, meta, social, schema) | `inc/seo/` |
| Theme functions (link list, analytics) | `inc/theme-functions/` |
| Image optimisation (WebP, regen) | `inc/img-optimization/` |
| REST API endpoints | `inc/rest/` |
| Scheduled events (cron) | `inc/core/` |
| Development tools (local only) | `inc/development/` |
| WooCommerce (disabled by default) | `inc/woocommerce/` |

---

## Directory Structure

```
basecamp/
  functions.php               Bootstrap — all require_once calls live here
  inc/
    class-basecamp.php         Theme setup, image sizes, menus, body classes
    frontend/
      class-basecamp-frontend.php   output_critical_css(), page_navi(), related_posts(), etc.
      class-basecamp-svg-icons.php  Centralised SVG icon registry
      remove-bloat.php              Strips unused WordPress default output
      class-basecamp-toast.php      Dismissable announcement bar
      basecamp-page-helpers.php     Page conditional helpers
      basecamp-subnav.php           Contextual child/sibling subnav
    seo/
      basecamp-title-functions.php  Context-aware <title> via extension classes
      basecamp-meta-description-functions.php
      basecamp-social-meta-functions.php   Open Graph + Twitter Card
    admin/
      class-basecamp-admin.php      Login branding, dashboard, editor tweaks
      basecamp-admin-helpers.php    Sanitisers, Customizer helpers
      class-basecamp-settings.php   Theme Settings page (Appearance → Theme Settings)
      class-basecamp-docs.php       In-admin Docs viewer
      class-basecamp-page-theme.php Page → Theme assignment column + meta box
    img-optimization/
      basecamp-webp-functions.php   Frontend WebP URL substitution
      basecamp-webp-conversion.php  Upload-time JPEG/PNG → WebP conversion
      basecamp-thumb-regen.php      Thumbnail regeneration tool
    core/
      basecamp-scheduled-events.php  Cron intervals, scheduling, callbacks
    rest/
      basecamp-rest-endpoints.php    REST routes under basecamp/v1
    woocommerce/
      woocommerce-functions.php      WooCommerce theme support scaffold
    theme-functions/
      basecamp-meta-link-list.php    Link list repeater meta box
      basecamp-analytics.php         GA4 integration
      basecamp-cpt-scaffold.php      Example CPT + taxonomy (disabled by default)
      basecamp-category-url.php      Category URL rewrite (disabled by default)
    development/
      class-basecamp-development.php  DevPilot local debug bar
  Docs/
    developer/                Module-level reference docs
    planning/                 Project roadmap and todos
```

---

## Child Theme Usage

Each project using Basecamp as a parent gets its own child theme. The child:

- Declares `Template: basecamp` in its `style.css` header
- Has its own `functions.php` that only loads project-specific modules
- Never re-requires any parent module file
- Never redefines any `Basecamp\*` namespaced class

### Child theme `functions.php` rules

| Do | Don't |
|---|---|
| `require_once` new project modules under `inc/` | Re-require parent modules |
| Call `Basecamp\Admin\Settings::get()` | Call `Basecamp_Settings::init()` (parent already does this) |
| Override `add_theme_support()` via `after_setup_theme` | Duplicate parent setup logic |
| Declare global template functions in `functions.php` (no namespace) | Define global functions inside a namespaced `inc/` file |

### Namespacing

Child theme classes live under a project-specific namespace (e.g. `Kaneism\ThemeFunctions`). Any function called directly from a template must be wrapped as a plain global function in the child's `functions.php`:

```php
// In child functions.php — exposes namespaced helper to templates
function my_theme_helper( ?int $post_id = null ): array {
    return \MyTheme\ThemeFunctions\my_theme_helper( $post_id );
}
```

### Extending theme supports

To add `post-thumbnails` support for a custom post type registered by a plugin:

```php
// In child functions.php
add_action( 'after_setup_theme', function (): void {
    add_theme_support( 'post-thumbnails', [ 'post', 'page', 'my-cpt' ] );
} );
```

---

## Conventions

### Naming

| Thing | Convention | Example |
|---|---|---|
| Classes | `Basecamp_*` | `Basecamp_Frontend` |
| Functions | `basecamp_*` | `basecamp_daily_maintenance_callback` |
| Hooks (actions/filters) | `basecamp_*` | `basecamp_body_page_classes` |
| Text domain | `basecamp` | `__( 'Text', 'basecamp' )` |
| Image size handles | `basecamp-img-*` | `basecamp-img-xl` |
| CSS classes | BEM | `card__picture`, `hero__img` |

### File placement

- New PHP modules → `inc/` subdirectory matching the area, loaded in `functions.php`
- New page templates → theme root (alongside `page-home.php`)
- New template parts → `template-parts/`
- New SCSS components → `assets/css/scss/basecamp-global-layout/components/`

### Escaping

Follow WordPress patterns already present — `esc_html()`, `esc_url()`, `esc_attr()`, `wp_kses_post()`. Never echo raw user data or unescaped option values.

---

## Extending

### Body classes

Add page-specific body classes via the filter — never hardcode page slugs in the theme:

```php
add_filter( 'basecamp_body_page_classes', function( $map ) {
    $map['contact'] = 'is--contact';
    $map['shop']    = 'has--breadcrumb';
    return $map;
} );
```

### SEO title extensions

Extend for a new CPT or plugin by adding a class to `Basecamp_Title_Manager::$extensions` in `basecamp-title-functions.php`:

```php
class Basecamp_Title_My_CPT extends Basecamp_Title_Extension {
    public function maybe_title(): ?string {
        if ( is_singular( 'my-cpt' ) ) {
            return get_the_title() . ' — My CPT';
        }
        return null;
    }
}
```

### Navigation menus

Register additional menu locations via the `basecamp_register_nav_menus` filter:

```php
add_filter( 'basecamp_register_nav_menus', function( $menus ) {
    $menus['my-location'] = __( 'My Location', 'basecamp' );
    return $menus;
} );
```

### Image sizes

Add custom sizes in `inc/class-basecamp.php` alongside the existing `add_image_size()` calls. Run `wp media regenerate --yes` after adding a new size.

---

## SCSS Build

Source lives in `assets/css/scss/`. The Live Sass Compiler extension compiles to `assets/css/build/*.min.css` on save. Commit both source and compiled files — the compiled file is what the browser loads.

See [Docs/developer/04-scss-system.md](Docs/developer/04-scss-system.md) for breakpoints, barrel file conventions, and the responsive coordinator pattern.

---

## WooCommerce

WooCommerce support is included in `inc/woocommerce/woocommerce-functions.php` but disabled in the parent by default. Activation is done in the **child theme**:

1. Install and activate the WooCommerce plugin.
2. In the child theme's `functions.php`, add WooCommerce theme support:
   ```php
   add_action( 'after_setup_theme', function (): void {
       add_theme_support( 'woocommerce' );
   } );
   ```
3. To enable the parent's WooCommerce scaffold (sidebar removal, WC-specific hooks), uncomment in the **parent** `functions.php`:
   ```php
   require_once __DIR__ . '/inc/woocommerce/woocommerce-functions.php';
   ```

---

## Developer Docs

Full module-level reference is in [`Docs/developer/`](Docs/developer/):

| File | Covers |
|---|---|
| `00-setup.md` | Install, plugins, first-run checklist |
| `01-architecture.md` | Module system, load order, parent/child structure |
| `02-code-style.md` | Naming, escaping, class patterns |
| `03-metaboxes.md` | Link list, video carousel, page→theme meta boxes |
| `04-scss-system.md` | Dart Sass, breakpoints, responsive coordinator |
| `05-images-media.md` | Image sizes, WebP pipeline, `output_critical_css()` |
| `06-seo.md` | Title manager, meta descriptions, Open Graph |
| `07-theme-settings.md` | Appearance → Theme Settings reference |
| `08-frontend-helpers.md` | Toast, subnav, page conditionals, RemoveBloat |

---

## License

WTFPL — do whatever you want with it.