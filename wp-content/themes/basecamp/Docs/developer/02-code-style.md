# Code Style Guide

Basecamp Coding & Architecture Guide — v1.0

---

## 1. Goals

- Predictable structure
- Fast iteration in development (no hidden caching)
- Safe extensibility (prefixing, no globals leakage)
- Clean separation: data (queries) vs presentation (templates)

---

## 2. PHP Standards

- Base: PSR-12 formatting where it doesn't conflict with WordPress Core guidelines
- Indent: 4 spaces (tabs in WordPress Core files, spaces in theme files)
- Line length soft limit: 120 (no hard wrap if readability suffers)
- One feature per file — avoid giant catch-all utility files
- **`declare(strict_types=1)`** must be the first statement in every `.php` file in `inc/` (after `<?php` and before `namespace`)
- **`final class`** on every class that is not explicitly designed to be extended. If a class needs to be subclassed, document why in a docblock comment

---

## 3. Naming Conventions

| Type | Pattern | Example |
|------|---------|---------|
| PHP namespace | `Basecamp\<Area>` | `Basecamp\Frontend`, `Basecamp\SEO` |
| Class name (namespaced) | PascalCase, no prefix | `Frontend`, `MetaLinkList`, `TitleManager` |
| Functions (global) | `basecamp_<domain>_<action>()` | `basecamp_posts_get_related()` |
| Filters / actions | `basecamp_<area>_<thing>` | `basecamp_query_args` |
| Meta keys | `_basecamp_<context>_<name>` | `_basecamp_post_featured` |
| Template parts | `template-parts/<area>/<component>.php` | `template-parts/content/card.php` |
| Variables | Semantic names | `$post`, `$items` (not `$arr`, `$data`) |
| Constants | `BASECAMP_<NAME>` | `BASECAMP_GA_MEASUREMENT_ID` |

---

## 3a. PHP Namespaces

All classes under `inc/` are namespaced. The hierarchy maps to concern areas:

| Namespace | Classes |
|---|---|
| `Basecamp` | `Theme` — core theme bootstrap |
| `Basecamp\Admin` | `Admin`, `Settings`, `Docs`, `AdminHelpers` |
| `Basecamp\Frontend` | `Frontend`, `SVGIcons`, `CookieConsent`, `Toast`, `VideoCarouselMetabox`, `RemoveBloat` |
| `Basecamp\SEO` | `TitleCore`, `TitleManager`, `MetaDescription`, `SocialMeta`, `Schema` |
| `Basecamp\Core` | `ScheduledEvents` |
| `Basecamp\ThemeFunctions` | `Analytics`, `MetaLinkList`, `CategoryURL` |
| `Basecamp\Ecommerce` | `WooCommerceIntegration` |
| `Basecamp\Development` | `Development` |

### Adding a new class

1. Create the file under the appropriate `inc/<area>/` directory.
2. Declare `namespace Basecamp\<Area>;` as the first statement after `<?php`.
3. Define the class. Static-method classes self-boot with a `ClassName::init();` call at the bottom; instantiated classes are wired in `functions.php`.
4. Add the `require_once` to `functions.php` in the correct section group.
5. WP core classes referenced inside a namespace must be prefixed with `\`: `\WP_Query`, `\WP_Post`, `\WP_Admin_Bar`.

```php
<?php
declare(strict_types=1);
// inc/theme-functions/class-basecamp-my-feature.php

namespace Basecamp\ThemeFunctions;

use Basecamp\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class MyFeature {

    public static function init(): void {
        add_action( 'wp_head', [ __CLASS__, 'output' ] );
    }

    public static function output(): void {
        $value = Settings::get( 'my_key' );
        // ...
    }
}

MyFeature::init();
```

### Template-callable global functions

Functions called directly from template files (e.g. `basecamp_get_link_list()`) **must be declared in `functions.php`**, not inside a namespaced `inc/` file. PHP scopes bare functions to whatever namespace is active in the file — declaring them in a namespaced file silently breaks global resolution.

### Back-compat aliases

Two `class_alias()` bridges are registered in `functions.php`:

| Alias | Resolves to |
|---|---|
| `Basecamp_Frontend` | `Basecamp\Frontend\Frontend` |
| `Basecamp_Settings` | `Basecamp\Admin\Settings` |

These exist because `header.php` and the WebP files reference the old names. Do not add new aliases unless there is a concrete back-compat requirement.

---

## 4. Escaping & Sanitization Rules

| Context | Function |
|---------|----------|
| Attribute values | `esc_attr()` |
| URLs (href/src) | `esc_url()` |
| Raw text node | `esc_html()` |
| Already-safe HTML | `wp_kses_post()` (minimize use) |
| Meta save | `sanitize_text_field()`, `esc_url_raw()` |
| Checkbox | `'1'` or `'0'` |

> Always sanitize on **input** (save) and escape on **output** (render).

---

## 5. Internationalization

- Text domain: `basecamp` (standardized across all files)
- All strings wrapped in `__()`, `_e()`, `_x()`, `esc_html__()` etc.
- Provide translator comments when variable interpolation is non-obvious
- Avoid concatenation inside translation functions — use `sprintf()`

---

## 6. Data vs Presentation

- Query helpers return raw `WP_Post[]` — no markup
- Render helpers accept prepared posts and only handle HTML and escaping
- Template parts only display — no queries inside unless a trivial fallback

---

## 7. Security

- Nonces required for all POST meta operations
- Capability checks: always `current_user_can('edit_post', $post_id)` etc.
- Never trust `$_REQUEST` — restrict to `$_POST` where appropriate
- Path traversal: always `realpath()` + `str_starts_with($real, $base)` when reading files

---

## 8. JavaScript

- Wrap in IIFE or module pattern
- Data passed via `wp_localize_script()` or `wp_add_inline_script()` — no inline globals
- Prefer vanilla JS; jQuery only acceptable in admin
- Single global `basecampApp` if a shared namespace is needed

---

## 9. CSS / SCSS

- Naming: BEM — `block__element--modifier`
- Keep admin CSS separate from frontend CSS
- Avoid `!important` — create utility classes if repetition emerges
- SCSS compiled by Live Sass Compiler (VS Code) → `assets/css/build/*.min.css`

---

## 10. Assets Versioning

- Development: `filemtime()` or theme version constant
- Production: `wp_get_theme()->get('Version')` (change in `style.css` to bust cache)

---

## 11. Hooks & Deprecation

- Anonymous closures are acceptable for local one-off logic (e.g. admin column output)
- Prefer named functions for anything reusable or testable
- Deprecation path: keep old function calling new one for at least one minor version
- Add `basecamp_deprecated_function($old, $version, $replacement)` wrapper if APIs evolve

---

## 12. Error Logging

- Use `error_log()` only inside `WP_DEBUG` checks
- Prefer a central helper if logging becomes frequent:

```php
function basecamp_log( string $message ): void {
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        error_log( '[basecamp] ' . $message );
    }
}
```

---

## 13. PR / Change Checklist

- [ ] Sanitization present on all input
- [ ] Escaping on all output
- [ ] i18n wrappers on all user-facing strings
- [ ] No transients unless caching is explicitly intended
- [ ] No large anonymous closures for reusable logic
- [ ] Template parts contain no queries

---

## 14. Future Roadmap

- REST read endpoints for content data (public fields only)
- Block editor (Gutenberg) opt-in helpers
- Accessibility audit (ARIA labels on all interactive areas)
- PHPCS with WordPress-Extra ruleset
