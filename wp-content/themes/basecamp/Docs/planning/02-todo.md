# Todo

Running task list. Items move here from the Roadmap when they are actively being worked. Completed items are checked and left for reference until the next cleanup pass.

Format: `[ ]` not started · `[x]` done · `[-]` deferred/blocked

---

## In Progress

*(nothing active)*

---

## Phase 1 — Foundation

### Code

- [x] Strip `Basecamp_Title_Sector` and `Basecamp_Title_Work` from `inc/seo/basecamp-title-functions.php` — starter should only ship `Basecamp_Title_Core`
- [x] Replace hardcoded `is_page('Contact')` and `is_page('Shop')` in `class-basecamp.php` `body_classes()` with a filterable generic pattern
- [x] Tidy `functions.php` load order — group into logical sections with clear comments
- [x] Confirm `Basecamp_Schema::init()` is called — verify `class-basecamp-schema.php` is initialised
- [x] Review `inc/rest/basecamp-rest-endpoints.php` — remove debug `error_log` calls; decide on example endpoint vs documented-only
- [x] Audit `inc/admin/class-basecamp-admin.php` for any remaining project-specific hardcoding
- [x] Fix `get_basecamp_directory_uri()` → `get_template_directory_uri()` in admin class (login_css / admin_css)
- [x] Remove project-specific sector/services dead code from `class-basecamp-schema.php`; clean hardcoded org defaults
- [-] Clean `header.php` — handled manually by project owner
- [x] Clean `footer.php` — legacy commented-out blocks removed

### Documentation

- [x] Rewrite `Docs/developer/01-architecture.md` — fix directory structure, module order, REST namespace, remove service worker section
- [x] Update `Docs/developer/00-setup.md` — remove project-specific plugin list (Media Library Folders, CF7), make generic
- [x] Update `Docs/developer/06-seo.md` — remove project-specific title extensions, sector archive filters, Service Schema section, clean schema output table
- [x] Update `Docs/developer/02-code-style.md` — generic naming examples, updated Future Roadmap
- [x] Update `Docs/developer/05-images-media.md` — remove Media Library Folders section, fix image size table, fix function name
- [x] `Docs/developer/03-metaboxes.md` — reviewed, no changes needed
- [x] `Docs/developer/04-scss-system.md` — reviewed, structure already accurate

### Deferred

- [-] PHP namespace strategy — design decision required before any action. See `00-overview.md`.

---

## Phase 2 — Features

- [x] Custom Post Type scaffold — example CPT in `inc/theme-functions/`, commented, not active by default
- [x] Search template improvements — `search.php` and `searchform.php` accessibility and usability
- [x] Pagination — confirm `Basecamp_Frontend::page_navi()` works; document usage
- [x] Image helper patterns — document responsive + WebP-aware image output
- [x] `wp-config-sample.php` — add security constants, debug flags, environment detection
- [x] Scheduled events — add at least one documented example to the stub

### Deferred

- [-] Navigation patterns — defer; will address when building out a real project on this base
- [-] Comments template — defer; likely not needed for modern builds
- [-] Review `robots.txt` — defer to Phase 3 / distribution prep

---

## Phase 3 — Polish & Distribution

- [x] README.md at theme root — install, extend, conventions
- [x] DocBlock audit — all classes and public methods
- [x] `style.css` theme header — version, description, tags
- [-] Update `screenshot.png` — manual; no code change needed
- [x] WooCommerce scaffold review and activation docs
- [x] SCSS system documentation — `04-scss-system.md` reviewed; accurate, no changes needed
- [-] Performance audit on clean WP install — manual; run against clean WP before distributing
- [x] PHP namespace migration — `Basecamp\*` hierarchy implemented across all `inc/` classes; back-compat aliases in `functions.php`; docs and copilot-instructions updated
- [x] Full Docs review pass before distribution — all developer docs reviewed and updated against current codebase
- [x] Theme Settings page — `Basecamp_Settings` class, Appearance → Theme Settings; GA4 ID, cookie compliance toggle, GSC verification, schema toggle, WebP toggle; `07-theme-settings.md` added
- [x] `declare(strict_types=1)` and `final class` — documented in `02-code-style.md` as explicit required standards; boilerplate example updated
- [x] `03-metaboxes.md` — both existing metaboxes (MetaLinkList, VideoCarousel) documented with field tables, meta keys, and template usage
- [x] `08-frontend-helpers.md` — new doc covering Toast, Subnav, Page Helpers, normalize_img_tag_classes, RemoveBloat additions (CF7, subscriber admin bar, wpautop excerpt), view-in-browser node, analytics filters

---

## Phase 4 — Parent / Child Architecture

- [x] Define the parent/child contract — Model B; infrastructure in parent, project-specific in child
- [x] Parent `header.php` — hardcoded "Basecamp" replaced with `get_bloginfo('name')`; `basecamp_header_logo` filter added
- [x] Parent `footer.php` — hardcoded legal links replaced with `basecamp_footer_legal_links` filter
- [x] Child theme scaffold — `kaneism/` with `style.css`, `functions.php`, `readme.md`, structured `inc/` (admin, frontend, theme-functions)
- [x] Documented child `functions.php` — all parent filters shown with inline examples; what-not-to-do rules
- [x] CPT scaffold in child — `kaneism-cpt-portfolio.php` as copy/rename template
- [-] Asset strategy — deferred
- [ ] Template override documentation — which parent templates are safe to override; filter hook alternatives
- [ ] Hook reference doc — all parent-exposed filters and actions enumerated for child theme use
- [ ] Build out Kaneism site content

---

## Completed

### Phase 4 Prep — Merge passes (Dish → Basecamp, Mi Concept → Basecamp)

- [x] Merge Pass 1 (Dish): Toast (`class-basecamp-toast.php`), Subnav (`basecamp-subnav.php`), Page Helpers (`basecamp-page-helpers.php`), Media (`basecamp-media.php`), Thumb Regen (`basecamp-thumb-regen.php`)
- [x] Merge Pass 1: Image Tools hub (tabbed: WebP Conversion / Regen / Test); `admin_init` form handling; progress fix
- [x] Merge Pass 1: 4 square image sizes added to `class-basecamp.php`; responsive-embeds enabled; widgets_init removed
- [x] Merge Pass 1: RemoveBloat — `wpautop` from excerpt, `hide_admin_bar_for_subscribers`, `conditionally_load_cf7` with `basecamp_cf7_page_slugs` filter
- [x] Merge Pass 1: Frontend — `remove_p_tags_from_images`, 3-layer `menu_selected_class`, output_critical_css BOM+cast fixes
- [x] Merge Pass 1: Admin — H2 added to TinyMCE; footer credit → kaneism.com
- [x] Merge Pass 1: All SEO files — `final` + `strict_types`; hierarchical title fix
- [x] Merge Pass 2 (Mi Concept): Category URL rewrite (`basecamp-category-url.php`) — disabled by default
- [x] Merge Pass 2: WebP — simplified `is_supported()` (str_contains, IE/EdgeHTML exclusion); static per-request cache in `get_webp_image()`
- [x] Merge Pass 2: Admin — `add_view_in_browser_node()` on post edit screens
- [x] Merge Pass 2: Frontend — `normalize_img_tag_classes()` + `basecamp_keep_wp_image_size_classes` filter
- [x] Announcement Bar settings (`toast_enabled`, `toast_text`, `toast_url`) added to Theme Settings

### Phase 1 — Foundation

- [x] Strip `Basecamp_Title_Sector` / `Basecamp_Title_Work` from title functions
- [x] Replace hardcoded `body_classes()` page checks with `basecamp_body_page_classes` filter
- [x] Tidy `functions.php` load order with labeled sections
- [x] Confirm `Basecamp_Schema::init()` is called
- [x] Clean REST endpoints — verified `basecamp/v1`, no debug calls
- [x] Fix `get_basecamp_directory_uri()` → `get_template_directory_uri()` in admin class
- [x] Fix `'text_domain'` → `'basecamp'` in admin class `replace_howdy()`
- [x] Remove sector/services dead code from schema class; clean hardcoded org defaults
- [x] Clean `footer.php`
- [x] Rewrite `01-architecture.md`
- [x] Update `00-setup.md`
- [x] Update `06-seo.md`
- [x] Update `02-code-style.md`
- [x] Update `05-images-media.md`
