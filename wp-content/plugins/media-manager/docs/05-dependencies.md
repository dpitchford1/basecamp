# Media Manager — Dependencies Audit

**Status:** ✅ Complete  
**Last updated:** 2026-05-04  
**Source audited:** `media-library-plus/libs/`, `media-library-plus/js/`, `media-library-plus/css/`, `media-library-plus/fonts/`

---

## 1. Bundled JavaScript Libraries

All JS libraries are bundled locally inside the plugin — no CDN or remote loading.

| Library | Version | Location | Purpose | Keep? |
|---|---|---|---|---|
| **jsTree** | 3.x | `js/jstree/` | Folder tree UI widget (left panel) | 🔄 Evaluate — functional but dated; assess in PRD phase |
| **Select2** | 4.x | `js/select2/` | Styled `<select>` dropdown for page picker | ❌ Drop — only used for the BDA no-access page dropdown; overkill dependency for one field |
| **Font Awesome** | 6.0.0 | `libs/fontawesome-free-6.0.0-web/` | Icon set for toolbar buttons and help panel | 🔄 Evaluate — if Basecamp/theme already loads FA, reuse; otherwise bundle selectively |

### Plugin-authored JS files

| File | Purpose | Keep? |
|---|---|---|
| `js/mlfp-media.js` | Main library admin UI — folder tree init, file grid, drag-and-drop, toolbar actions | ✅ Keep (rewrite) |
| `js/uploads-media.js` | Upload panel — drag-and-drop upload, progress | ✅ Keep (rewrite) |
| `js/mgmlp-loader.js` | Loader/spinner utility | ✅ Keep (simplify) |
| `js/bda-media.js` | BDA UI — block/unblock toggle, IP management, download link config | 🔄 Keep partial (rewrite; drop download link UI) |
| `js/mlfp-lpf.js` | BDA — load protected file on edit-attachment admin screen | ❌ Drop (tied to frontend image proxy) |

---

## 2. Bundled CSS

| File / Location | Purpose | Keep? |
|---|---|---|
| `css/` (plugin-authored) | Library admin styles, folder tree, toolbar, grid, BDA panel | ✅ Keep (rewrite from scratch) |
| Font Awesome CSS | Icons | 🔄 Evaluate (see above) |

---

## 3. Bundled Fonts

| Item | Purpose | Keep? |
|---|---|---|
| `fonts/` | Font Awesome web fonts | 🔄 Evaluate with FA decision |

---

## 4. PHP Libraries / Helpers

| File | Purpose | Keep? |
|---|---|---|
| `includes/attachments.php` | Helper functions: `str_begins_with()`, `get_file_attachment_id()` | 🔄 Keep — `get_file_attachment_id()` is a useful utility; port and namespace it |

No Composer dependencies. No autoloader. All PHP is manually `include`/`require` loaded.

---

## 5. External API Integrations

**None.** The plugin makes no calls to external APIs or remote services. All operations are local filesystem + WP database.

---

## 6. WordPress Plugin Dependencies

The plugin contains conditional checks for the following external plugins — all of which are being **dropped**:

| Plugin | Check Method | Used For | Drop? |
|---|---|---|---|
| MaxGalleria / MaxGalleria Pro | `is_plugin_active()` + version check | "Add to Gallery" feature | ❌ Drop |
| NextGen Gallery | Implicit (AJAX endpoint) | `mlpp_create_new_ng_gallery` | ❌ Drop |

**No plugin dependencies in the rebuild.** Media Manager must function as a fully standalone plugin.

---

## 7. WordPress Core Dependencies

The plugin relies on the following WP core APIs — all standard, all kept:

| Core API / Function | Purpose |
|---|---|
| `wp_handle_upload()` | File upload processing |
| `wp_generate_attachment_metadata()` | Thumbnail generation after upload |
| `wp_insert_attachment()` | Register uploaded file as WP attachment |
| `wp_delete_attachment()` | Remove attachment from WP |
| `wp_update_attachment_metadata()` | Update attachment record |
| `wp_upload_dir()` | Get upload directory paths |
| `dbDelta()` | Create/upgrade custom DB tables |
| `wp_schedule_event()` | Schedule daily folder scan cron |
| `WP_Filesystem` | Used for `.htaccess` writes in BDA |
| `add_submenu_page()` / `add_menu_page()` | Admin menu registration |
| `wp_create_nonce()` / `wp_verify_nonce()` | All AJAX security |
| `current_user_can()` | Capability checks throughout |

---

## 8. Rebuild Dependency Strategy

| Category | Decision |
|---|---|
| JS libraries | Bundle only what's needed. Evaluate jsTree vs. a lighter alternative in PRD phase. Drop Select2. |
| Icons | If Basecamp theme loads Font Awesome globally in admin, reuse. Otherwise bundle only the icon subset needed. |
| PHP helpers | No external PHP dependencies. All utilities written in-house and namespaced under `MediaManager\`. |
| Composer | Not required for v1. May add for test tooling only. |
| External APIs | None. |
| Plugin deps | None. Fully standalone. |
