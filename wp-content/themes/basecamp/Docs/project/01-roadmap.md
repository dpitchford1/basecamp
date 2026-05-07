# Roadmap

Work is broken into three phases. Each phase must be substantially complete before the next begins — the foundation has to be solid before building upward.

---

## Phase 1 — Foundation (Current)

Goal: A clean, honest starter theme with no client-project residue, accurate documentation, and a codebase another developer can read and trust.

### Code

- [x] **Strip project-specific SEO title extensions** — `Basecamp_Title_Sector` and `Basecamp_Title_Work` removed. Core ships `Basecamp_Title_Core` only.
- [x] **Audit `class-basecamp.php` body classes** — `is_page('Contact')` / `is_page('Shop')` replaced with the filterable `basecamp_body_page_classes` filter.
- [x] **Tidy `functions.php` load order** — grouped into labeled sections: Core → Frontend → Admin → SEO → Theme Functions → Optimization → REST → Cron → Dev → Ecommerce.
- [x] **Confirm `class-basecamp-schema.php` is initialised** — verified.
- [x] **Review `inc/rest/basecamp-rest-endpoints.php`** — debug `error_log` calls removed; starter ping endpoint kept and documented.
- [x] **Review `inc/admin/class-basecamp-admin.php`** — project-specific hardcoding removed; `get_basecamp_directory_uri()` → `get_template_directory_uri()` fixed.
- [-] **Clean `header.php` and `footer.php`** — `footer.php` cleaned; `header.php` handled manually by project owner.
- [x] **PHP namespace strategy** — `Basecamp\*` hierarchy implemented across all `inc/` classes. Back-compat aliases registered in `functions.php`.

### Documentation

- [x] **Update `Docs/developer/01-architecture.md`** — directory structure, module load order, and new modules (toast, subnav, page helpers, media) all updated.
- [x] **Update `Docs/developer/00-setup.md`** — project-specific plugin list removed; made generic and accurate.
- [x] **Update `Docs/developer/06-seo.md`** — project-specific title extensions removed; schema output table cleaned.
- [x] **Review all other developer docs** — `02` through `05` reviewed and updated. New `07-theme-settings.md` and `08-frontend-helpers.md` added.

---

## Phase 2 — Features

Goal: Enrich the starter with additional built-in features that reduce plugin dependency without adding complexity for projects that don't need them. Everything toggleable.

- [x] **Custom Post Types scaffold** — `inc/theme-functions/basecamp-cpt-scaffold.php` added; commented, not active by default.
- [-] **Navigation patterns** — deferred; will address when building out a real project on this base.
- [x] **Search template** — `search.php` and `searchform.php` updated for accessibility and usability.
- [-] **Comments template** — deferred; likely not needed for modern builds.
- [x] **Pagination helper** — `Basecamp_Frontend::page_navi()` confirmed working; usage documented in `05-images-media.md`.
- [x] **Image helper patterns** — documented in `05-images-media.md`; responsive, WebP-aware, lazy-load patterns all covered.
- [x] **`wp-config.php` sample additions** — security constants, debug flags, and environment detection added.
- [-] **Robots.txt review** — deferred to Phase 3 / distribution prep.
- [x] **Scheduled events** — `inc/core/basecamp-scheduled-events.php` stub populated with a documented example hook.

---

## Phase 3 — Polish & Distribution

Goal: A theme that can be handed to another developer (or future-self) and is immediately understandable.

- [x] **README.md at theme root** — comprehensive: what it is, requirements, how to install, how to extend, coding conventions.
- [x] **Inline code documentation** — DocBlock audit complete across all classes and public methods.
- [x] **`style.css` theme header** — version, description, and tags updated.
- [-] **Screenshot** — manual; update `screenshot.png` before distribution.
- [x] **WooCommerce** — toggle-ready scaffold confirmed; activation steps documented.
- [x] **SCSS system** — `04-scss-system.md` accurate; asset pipeline, file structure, and compilation fully documented.
- [-] **Performance audit** — manual; run against a clean WP install before distributing.
- [x] **PHP namespace migration** — completed. See Phase 1.
- [x] **Docs review pass** — all developer docs reviewed and updated against current codebase. New `07-theme-settings.md` and `08-frontend-helpers.md` added.

---

## Phase 4 — Parent / Child Architecture

Goal: Extract Basecamp into a true WordPress parent theme. Child themes inherit all core functionality and override only what they need — zero copy-paste between projects.

- [x] **Define the parent/child contract** — infrastructure in parent, project-specific in child; Model B (parent ships opinionated defaults, child overrides selectively).
- [x] **Child theme scaffold** — `kaneism/` created: `style.css` with `Template: basecamp`, documented `functions.php`, `readme.md`, structured `inc/` with admin/frontend/theme-functions stubs.
- [-] **Asset strategy for child themes** — deferred; requires strategic decision on CSS architecture.
- [ ] **Template override documentation** — document which parent templates are safe to override, which use filter hooks, and how to extend without breaking.
- [ ] **Hook reference** — enumerate all filters and actions the parent exposes specifically for child theme use.
- [ ] **First real child theme** — Kaneism scaffold created; now build out the actual site content.
