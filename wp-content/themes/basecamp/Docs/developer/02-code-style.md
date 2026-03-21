# Code Style Guide

Basecamp Coding & Architecture Guide — v0.2

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

---

## 3. Naming Conventions

| Type | Pattern | Example |
|------|---------|---------|
| Functions | `basecamp_<domain>_<action>()` | `basecamp_posts_get_related()` |
| Filters / actions | `basecamp_<area>_<thing>` | `basecamp_query_args` |
| Meta keys | `_basecamp_<context>_<name>` | `_basecamp_post_featured` |
| Template parts | `template-parts/<area>/<component>.php` | `template-parts/content/card.php` |
| Variables | Semantic names | `$post`, `$items` (not `$arr`, `$data`) |
| Constants | `BASECAMP_<NAME>` | `BASECAMP_GA_MEASUREMENT_ID` |

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

- PHP namespace migration (`Basecamp\Inc\Frontend`, etc.)
- REST read endpoints for content data (public fields only)
- Block editor (Gutenberg) opt-in helpers
- Accessibility audit (ARIA labels on all interactive areas)
- PHPCS with WordPress-Extra ruleset
