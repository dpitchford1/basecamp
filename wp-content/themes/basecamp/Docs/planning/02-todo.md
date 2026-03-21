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
- [-] PHP namespace migration (if decided in Phase 1)
- [x] Full Docs review pass before distribution — all seven developer docs reviewed and updated
- [x] Theme Settings page — `Basecamp_Settings` class, Appearance → Theme Settings; GA4 ID, cookie compliance toggle, GSC verification, schema toggle, WebP toggle; `07-theme-settings.md` added

---

## Completed

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
