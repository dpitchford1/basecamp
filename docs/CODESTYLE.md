# Basecamp Coding & Architecture Guide (v0.1)

## 1. Goals
- Predictable structure
- Fast iteration in development (no hidden caching)
- Safe extensibility (prefixing, no globals leakage)
- Clean separation: data (queries) vs presentation (templates)

## 2. PHP Standards
- Base: PSR-12 formatting where it doesn’t conflict with WordPress Core guidelines.
- Indent: 4 spaces.
- Line length soft limit: 120 (no hard wrap if readability suffers).
- One feature per file. Avoid giant catch‑all utility files.

## 3. Naming Conventions
- Functions: basecamp_<domain>_<action>() e.g. basecamp_sector_register(), basecamp_sector_get_children()
- Filters / actions: basecamp_<area>_<thing> (e.g. basecamp_featured_projects_args)
- Meta keys: _basecamp_<context>_<name> (already in use).
- Template part files: template-parts/<area>/<component>.php
- Variables: $sector, $projects (semantic, no $arr / $data unless generic).
- Constants (optional future): BASECAMP_ENABLE_CACHE, BASECAMP_ENV.

## 4. File / Directory Layout (Theme)
```
inc/
  cpt/                (CPT registration & taxonomy)
  admin/              (metaboxes, admin columns, admin assets enqueue)
  frontend/           (render helpers, shortcodes, blocks)
  queries/            (data-layer: query + aggregation helpers)
  util/               (shared small helpers: escaping, env detect, feature toggles)
template-parts/
  sector/
    card.php
assets/
  css/
  js/
```
(Refactor gradually; no big‑bang rewrite.)

## 5. Hooks Strategy
- Registration hooks (init) stay in dedicated files (no anonymous closures for public APIs).
- Anonymous closures acceptable for extremely local logic (e.g. one-off admin column output) but prefer named for reuse/testing.
- Filters: always documented with phpdoc block (params + return type).

## 6. Data vs Presentation
- Query helpers return raw WP_Post[] (no markup).
- Render helpers accept prepared posts and only handle HTML and escaping.
- Template parts only display (no queries inside unless trivial fallback).

## 7. Escaping / Sanitization Rules
| Context                | Use                            |
|------------------------|--------------------------------|
| Attribute values       | esc_attr()                     |
| URLs (href/src)        | esc_url()                      |
| Raw text node          | esc_html()                     |
| Already-safe HTML      | wp_kses_post() (rare; minimize)|
| Meta save              | sanitize_text_field(), esc_url_raw() |
| Checkboxes             | presence → '1' else '0'        |

Always sanitize on input (save) and escape on output (render).

## 8. Internationalization
- Text domain: basecamp
- All strings wrapped in __(), _e(), _x(), esc_html__(), etc.
- Provide translator comments when variable interpolation is non-obvious.
- Avoid concatenation inside translation functions; use sprintf.

## 9. Assets
- Admin assets live under inc/admin/assets/ (already aligned).
- Frontend assets under assets/{css,js}/ (consider build step later).
- Versioning: filemtime() in dev; switch to BASECAMP_ASSET_VERSION constant in production.

## 10. Caching Policy
- Development: disabled (no transients, no object cache assumptions).
- Production: opt‑in via BASECAMP_ENABLE_CACHE true (future toggle).
- When reintroducing: central basecamp_cache() wrapper with strategy (object cache > transient > noop).

## 11. Environment Detection
Add helper (future):
```
function basecamp_env() {
    if (defined('BASECAMP_ENV')) return BASECAMP_ENV;
    return (defined('WP_DEBUG') && WP_DEBUG) ? 'dev' : 'prod';
}
```

## 12. Security
- Nonces required for all POST meta operations.
- Capability checks always current_user_can('edit_post', $post_id) etc.
- Never trust $_REQUEST; restrict to $_POST where appropriate.

## 13. Featured Projects Strategy
- Binary flag now; roadmap: numeric rank meta (_basecamp_project_featured_rank) for manual ordering.
- When rank exists: sort by rank ASC then date DESC.

## 14. Dates
- Store original human entry (_basecamp_project_date).
- Store normalized ISO (_basecamp_project_date_iso) for sorting / machine use.
- Always prefer ISO when building queries.

## 15. Deprecation Path
- Add basecamp_deprecated_function( $old, $version, $replacement ) wrapper if APIs evolve.
- Grace period: keep old function calling new for at least one minor version.

## 16. JavaScript
- Wrap in IIFE or module pattern. Use single global basecampApp if needed.
- Data passed via wp_localize_script or wp_add_inline_script (no inline globals sprinkled).
- Prefer vanilla JS unless WP dependency required (jQuery OK in admin only).

## 17. CSS
- Naming: block--element--modifier or simplified BEM (e.g. featured-projects-list, fp-thumb).
- Keep admin CSS separate from frontend.
- Avoid !important; create utility classes if repetition emerges.

## 18. Documentation
- Each public function: phpdoc with @param types, @return, @since (future).
- Complex filters: add example usage in docblock.

## 19. Testing / QA (Future)
- Introduce PHPCS with WordPress-Extra + custom exclusions.
- Optional: wp scaffold plugin-tests for a small bootstrap if complex logic emerges.

## 20. Migrations
- Add /inc/upgrade/ folder for one-off data migrations (e.g., populating ISO date).
- Guard by option flag (e.g. basecamp_migration_iso_dates_done).

## 21. Error Logging
- Use error_log only inside WP_DEBUG checks.
- Provide basecamp_log($message) helper to centralize format + toggle.

## 22. Future Namespacing (Optional)
Transition path:
- New code: namespace Basecamp\Sectors;
- Legacy functions preserved as wrappers calling new namespaced classes/functions.

## 23. Pull Request Checklist (Internal)
- Sanitization present.
- Escaping on output.
- i18n wrappers.
- No transients if BASECAMP_ENABLE_CACHE not defined true.
- No large anonymous closures for reusable logic.
- Template parts contain no queries.

## 24. Roadmap Items
- Ranking for featured.
- REST read endpoint (public fields only).
- Block or shortcode for Featured Projects.
- Optional GraphQL compatibility (if WPGraphQL installed).
- Accessibility audit (ARIA labels on interactive areas).

---

Short, opinionated, evolves with project needs. Update version when adjusting practices.
