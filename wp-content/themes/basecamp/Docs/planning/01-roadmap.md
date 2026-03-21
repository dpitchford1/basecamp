# Roadmap

Work is broken into three phases. Each phase must be substantially complete before the next begins — the foundation has to be solid before building upward.

---

## Phase 1 — Foundation (Current)

Goal: A clean, honest starter theme with no client-project residue, accurate documentation, and a codebase another developer can read and trust.

### Code

- [ ] **Strip project-specific SEO title extensions** — `Basecamp_Title_Sector` and `Basecamp_Title_Work` exist from the original project. Core should only ship `Basecamp_Title_Core`. Move the others to a documented example or remove entirely.
- [ ] **Audit `class-basecamp.php` body classes** — `is_page('Contact')` and `is_page('Shop')` are project-specific. Replace with a filterable, generic approach.
- [ ] **Tidy `functions.php` load order** — Analytics and cookie consent are loaded outside their logical section groups. Group into: Core → Frontend → Admin → SEO → Theme Functions → Optimization → REST → Cron → Dev → Ecommerce.
- [ ] **Confirm `class-basecamp-schema.php` is initialised** — verify `Basecamp_Schema::init()` is called.
- [ ] **Review `inc/rest/basecamp-rest-endpoints.php`** — strip debug `error_log` calls; decide if a starter example endpoint stays or is documented-only.
- [ ] **Review `inc/admin/class-basecamp-admin.php`** — audit for any remaining project-specific hardcoding.
- [ ] **Clean `header.php` and `footer.php`** — remove legacy commented-out blocks; leave minimal, intentional scaffolding with clear developer comments.
- [ ] **PHP namespace strategy** — evaluate moving from `Basecamp_*` prefix convention to `namespace Basecamp\...` for collision safety and future-proofing. *(Deferred — design decision needed first.)*

### Documentation

- [ ] **Update `Docs/developer/01-architecture.md`** — directory structure references non-existent paths (`template-parts/sector/`, `template-parts/news/`, CPTs). Rewrite to reflect actual codebase.
- [ ] **Update `Docs/developer/00-setup.md`** — remove project-specific plugin list; make generic and accurate.
- [ ] **Update `Docs/developer/06-seo.md`** — remove `Basecamp_Title_Sector` and `Basecamp_Title_Work` from the extension table; reframe as a documented pattern with a single example.
- [ ] **Review all other developer docs** — pass through `02` through `05` for any project-specific content.

---

## Phase 2 — Features

Goal: Enrich the starter with additional built-in features that reduce plugin dependency without adding complexity for projects that don't need them. Everything toggleable.

- [ ] **Custom Post Types scaffold** — a clean, commented example CPT registration in `inc/theme-functions/` that developers copy and adapt. No CPT active by default.
- [ ] **Navigation patterns** — clean primary nav with keyboard accessibility, mobile toggle, and aria attributes baked in. No jQuery.
- [ ] **Search template** — improve `search.php` and `searchform.php` for accessibility and usability.
- [ ] **Comments template** — `comments.php` currently missing from active theme; add a clean, accessible version.
- [ ] **Pagination helper** — `Basecamp_Frontend::page_navi()` exists but usage needs documenting; confirm it works correctly.
- [ ] **Image helper patterns** — document and standardize how images are output (responsive, WebP-aware, lazy-load).
- [ ] **`wp-config.php` sample additions** — security constants, debug flags, environment detection helpers for the repo sample.
- [ ] **Robots.txt review** — current `robots.txt` in repo root needs review for starter theme defaults.
- [ ] **Scheduled events** — populate `inc/core/basecamp-scheduled-events.php` stub with at least one documented example hook.

---

## Phase 3 — Polish & Distribution

Goal: A theme that can be handed to another developer (or future-self) and is immediately understandable.

- [ ] **README.md at theme root** — comprehensive: what it is, requirements, how to install, how to extend, coding conventions.
- [ ] **Inline code documentation** — audit all classes and functions for complete DocBlocks.
- [ ] **`style.css` theme header** — update version, description, and tags to reflect the actual theme.
- [ ] **Screenshot** — update `screenshot.png` to reflect the actual theme.
- [ ] **WooCommerce** — complete the toggle-ready scaffold: confirm `inc/woocommerce/woocommerce-functions.php` is production-ready, document activation steps.
- [ ] **SCSS system** — document the asset pipeline, file structure, and compilation setup clearly for new developers.
- [ ] **Performance audit** — run against a clean WP install, document baseline scores, identify any remaining low-hanging fruit.
- [ ] **PHP namespace migration** — if the namespace strategy decision (Phase 1) concluded in favour of migration, execute it here.
- [ ] **Docs review pass** — full review of all Docs sections against the shipped code before any distribution.
