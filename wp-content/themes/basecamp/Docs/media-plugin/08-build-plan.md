# Media Manager — Build Plan

**Document:** `08-build-plan.md`  
**Status:** ✅ Complete  
**Last updated:** 2026-05-05  
**Plugin path:** `wp-content/plugins/media-manager/`  
**Depends on:** `06-prd.md`, `07-architecture.md`

---

## Standing Constraints

These rules apply to **every phase**. No exceptions without an explicit decision recorded here.

### Admin UI — WordPress Native Styles Only

The admin interface must look and feel like WordPress. No custom admin CSS frameworks, no imported fonts, no third-party icon libraries except where explicitly approved below.

**Use these WP core classes:**

| Pattern | WP class / element |
|---|---|
| Page wrapper | `.wrap` |
| Settings form | `.form-table`, `<th scope="row">`, `<td>` |
| Tabbed navigation | `.nav-tab-wrapper`, `.nav-tab`, `.nav-tab-active` |
| Buttons | `.button`, `.button-primary`, `.button-secondary`, `.button-link-delete` |
| Notices | `.notice`, `.notice-success`, `.notice-error`, `.notice-warning` |
| Section headers | `<h1>`, `<h2 class="title">` |
| Help tabs | `$screen->add_help_tab()` — no custom slide-out panels |

**Do not use:**
- Custom admin fonts (`@import` of any web font in admin CSS)
- Font Awesome, Heroicons, or any icon font — Dashicons only for admin chrome
- Bootstrap, Tailwind, or any CSS framework
- Select2 — use native `<select>`

### Icons

The library toolbar uses icons. **Approved approach:** SVG sprite (`assets/images/icons.svg`) with `<use>` references. Subset only what is needed. No web font icon library.

Dashicons used for the admin menu item icon only.

### JavaScript

Admin JS: vanilla JS (ES6+). jQuery is acceptable where WP already ships it (e.g., AJAX calls use `wp.ajax` or plain `fetch`). Write our own code as vanilla where reasonable.

No frontend JS — Media Manager has zero frontend output.

### Third-Party Packages

| Package | Decision |
|---|---|
| jsTree 3.x | ✅ **Approved** — folder tree UI. Bundled in `assets/js/vendor/jstree/`. Evaluate replacement post-v1. |
| Font Awesome | ❌ **Removed** — SVG sprite only |
| Select2 | ❌ **Removed** — native `<select>` |
| Any Composer packages | ❌ **Not required for v1** |

---

## How This Works

Each phase has:
- A **goal** — what exists at the end of the phase
- A **file list** — every file created or modified
- A **confirmation checklist** — manually verify before moving to next phase

Phases are sequential. Do not start Phase N+1 until Phase N is confirmed ✅.

---

## Phase Overview

| # | Phase | Key deliverable | Status |
|---|---|---|---|
| 1 | Plugin Scaffold | Plugin activates, DB tables exist, cron scheduled, no errors | ✅ |
| 2 | CPT & Data Layer | `mm_folder` CPT registered; repositories wired; existing uploads scanned on fresh activate | ✅ |
| 3 | Admin Menu & Assets | Three admin pages register, load without errors; CSS/JS conditionally enqueued | ✅ |
| 4 | Folder Tree & Navigation | Folder tree renders via jsTree; clicking a folder loads AJAX file grid | ✅ |
| 5 | File Grid & Sorting | Files display for selected folder; sort by date/title/direction works | ✅ |
| 6 | Upload | Drag-and-drop + browse upload to selected folder; file appears in grid and WP library | ✅ |
| 7 | Move, Copy, Rename, Delete | All file operations work; embedded links update on move/rename | ✅ |
| 8 | Folder Operations | Create, delete, hide folder; refresh tree; daily cron scan | ✅ |
| 9 | Sync | Sync current folder imports FTP/server-added files; skips WebP if setting is on | ✅ |
| 10 | Settings Page | Options tab saves/reads; scaling toggle works; sort preference stored in user meta | ✅ |
| 11 | Thumbnail Regeneration | Thumbnails page queues and processes regen; progress works; SVGs skipped | ✅ |
| 12 | Block Direct Access | Protected dir, block/unblock files, IP list, .htaccess rules, no-access redirect | ✅ |
| 13 | Polish & Hardening | Help tabs, capability edge cases, nonce coverage, uninstall cleanup, QA pass | ✅ |

---

## Phase 1 — Plugin Scaffold

**Goal:** Plugin appears in WP Plugins list, activates without error, three DB tables exist, cron is scheduled, deactivation clears cron, plugin header constants defined.

### Files to create

```
media-manager/
├── media-manager.php
├── uninstall.php
└── includes/
    └── Core/
        ├── class-plugin.php
        ├── class-loader.php
        ├── class-activator.php
        └── class-deactivator.php
```

### Checklist

- [ ] Plugin appears in WP admin → Plugins list
- [ ] Activates without PHP errors or warnings
- [ ] `{prefix}mm_files` table exists after activation
- [ ] `{prefix}mm_protected` table exists after activation
- [ ] `{prefix}mm_blocked_ips` table exists after activation
- [ ] `mm_version` option written to `wp_options`
- [ ] `mm_folder_scan` cron event is scheduled (verify with WP Crontrol or `wp cron event list`)
- [ ] Deactivating plugin clears `mm_folder_scan` cron
- [ ] Re-activating does not duplicate tables or error (dbDelta is idempotent)
- [ ] Uninstalling (delete) drops all three tables and removes `mm_*` options

---

## Phase 2 — CPT & Data Layer

**Goal:** `mm_folder` CPT is registered. Repositories can read/write to all three custom tables. On fresh activation, existing WP uploads folder structure is scanned and imported as CPT posts with file-folder rows in `mm_files`.

### Files to create

```
includes/
├── CPT/
│   └── class-folder-post.php
├── Data/
│   ├── class-folder-repository.php
│   ├── class-file-repository.php
│   ├── class-protected-repository.php
│   └── class-ip-repository.php
└── Helpers/
    └── class-path-helper.php
```

### Checklist

- [ ] `mm_folder` CPT registered on `init`
- [ ] CPT is non-public, non-searchable, not in admin menu
- [ ] `FolderRepository::get_all()` returns all `mm_folder` posts
- [ ] `FolderRepository::get_children( $parent_id )` returns correct sub-folders
- [ ] `FileRepository::get_by_folder( $folder_id )` returns correct attachment IDs
- [ ] `FileRepository::insert( $attachment_id, $folder_id )` writes correct row
- [ ] `FileRepository::update_folder( $attachment_id, $folder_id )` updates row
- [ ] `FileRepository::delete( $attachment_id )` removes row
- [ ] On fresh activation: existing `wp-content/uploads` directories imported as `mm_folder` posts
- [ ] On fresh activation: existing attachment post IDs mapped to their folder in `mm_files`
- [ ] `PathHelper::url_to_path()` and `PathHelper::path_to_url()` convert correctly
- [ ] `PathHelper::is_path_inside( $parent, $child )` returns correct boolean

---

## Phase 3 — Admin Menu & Assets

**Goal:** Three admin pages register and load without errors. CSS and JS are conditionally enqueued — only on Media Manager pages. No assets on unrelated admin pages.

### Files to create

```
includes/
├── Admin/
│   ├── class-admin.php
│   ├── class-menu.php
│   └── class-assets.php
assets/
├── css/
│   ├── mm-admin.css
│   └── mm-admin.min.css
└── js/
    └── vendor/
        └── jstree/    ← copy from old plugin
```

### Checklist

- [ ] "Media Manager" top-level menu item appears in WP admin sidebar
- [ ] Menu item uses a Dashicon
- [ ] "Library", "Settings", "Thumbnails" sub-pages register
- [ ] Library page loads with `.wrap` wrapper; no PHP errors
- [ ] Settings page loads; no PHP errors
- [ ] Thumbnails page loads; no PHP errors
- [ ] `mm-admin.css` enqueued only on Media Manager admin pages
- [ ] No assets enqueued on Dashboard, Posts, or other unrelated admin pages
- [ ] Editor role can see and access all three pages
- [ ] Subscriber role cannot access any Media Manager page (403 / redirect)
- [ ] WP native help tabs present on each page (even if content is placeholder for now)

---

## Phase 4 — Folder Tree & Navigation

**Goal:** Library page renders a jsTree folder tree populated from `mm_folder` CPT posts. Clicking a folder fires an AJAX request and returns (initially empty) folder content panel.

### Files to create / modify

```
includes/
├── Admin/
│   └── class-library-page.php
├── Ajax/
│   └── class-ajax-handler.php   ← scaffold; mm_load_folder + mm_folder_contents
└── FileSystem/
    └── class-folder-manager.php  ← scaffold
assets/
└── js/
    ├── mm-library.js
    └── mm-library.min.js
```

### Checklist

- [ ] Left pane renders jsTree with correct hierarchy from DB
- [ ] Root node is the WP uploads folder
- [ ] Sub-folders nest correctly under parents
- [ ] Hidden folders (`_mm_hidden`) do not appear in tree
- [ ] Clicking a folder triggers `mm_load_folder` AJAX
- [ ] Right pane updates with folder name heading (files may be empty at this stage)
- [ ] Right-click context menu appears on folder nodes
- [ ] Context menu shows "Hide folder" and "Delete folder" options (actions wired in Phase 8)
- [ ] Refresh icon triggers `mm_refresh_folders` AJAX (wired in Phase 8)
- [ ] Nonce present in all AJAX payloads
- [ ] Requests without valid nonce return error; no processing occurs

---

## Phase 5 — File Grid & Sorting

**Goal:** Selecting a folder populates the file grid with thumbnails (and file-type icons for non-images). Sort by date/title and ASC/DESC work. Sort preference is stored per user and restored on next visit.

### Files to create / modify

```
includes/
├── Ajax/
│   └── class-ajax-handler.php   ← mm_folder_contents, mm_sort_contents
assets/
└── js/
    └── mm-library.js            ← grid render, checkbox logic, sort controls
```

### Checklist

- [ ] Selecting a folder loads its attachments into the file grid
- [ ] Images display as thumbnails; non-images display a file-type icon
- [ ] Each file has a checkbox
- [ ] Clicking a file opens WP's native edit-attachment screen in a new tab
- [ ] Select All checkbox checks/unchecks all visible files
- [ ] Shift-click selects a range of files
- [ ] Sort by Date reloads grid sorted by upload date
- [ ] Sort by Title reloads grid sorted by attachment title
- [ ] Reverse Order toggles ASC/DESC
- [ ] Sort preference (field + direction) saved to user meta after each change
- [ ] Sort preference restored correctly on next page load
- [ ] Items per page setting (`mm_items_per_page`) respected
- [ ] Large folder (500+ files) loads without timeout

---

## Phase 6 — Upload

**Goal:** Upload panel opens on toolbar click. Files dropped or browsed upload to the currently selected folder. Uploaded files appear in the file grid and WP Media Library.

### Files to create / modify

```
includes/
├── FileSystem/
│   └── class-file-manager.php   ← upload() method
├── Ajax/
│   └── class-ajax-handler.php   ← mm_upload_file
assets/
└── js/
    ├── mm-upload.js
    └── mm-upload.min.js
```

### Checklist

- [ ] Upload panel toggles open/closed via toolbar icon
- [ ] Drag-and-drop zone accepts multiple files
- [ ] Browse button opens OS file picker; single and multi-select work
- [ ] Files upload to the physically correct folder on disk (not default WP uploads/year/month)
- [ ] Each uploaded file appears in WP Media Library as an attachment
- [ ] File appears in current folder's grid immediately after upload (no page reload)
- [ ] `mm_files` table row written for each upload
- [ ] `upload_files` capability check enforced on AJAX handler
- [ ] Nonce verified before processing
- [ ] Invalid file types rejected (WP's standard filetype checks apply)
- [ ] Upload error messages surface to the user (not silently fail)
- [ ] `wp_generate_attachment_metadata` fires correctly (thumbnails generated)
- [ ] New attachment correctly added to selected folder via `wp_generate_attachment_metadata` filter

---

## Phase 7 — Move, Copy, Rename, Delete

**Goal:** All four file operation types work correctly. Move and rename update embedded links in post content.

### Files to create / modify

```
includes/
├── FileSystem/
│   ├── class-file-manager.php    ← move(), copy(), rename(), delete()
│   └── class-link-updater.php
├── Ajax/
│   └── class-ajax-handler.php    ← mm_move_copy_file, mm_rename_file, mm_delete_files
```

### Checklist

**Move:**
- [ ] Dragging a file to a folder tree node (move mode) triggers `mm_move_copy_file`
- [ ] Physical file moved to destination folder on disk
- [ ] All thumbnail variants moved to destination folder
- [ ] `_wp_attached_file` post meta updated on the attachment
- [ ] Old embedded URL replaced with new URL in all `wp_posts.post_content` rows
- [ ] `mm_files` table `folder_id` updated
- [ ] `mm_before_file_move` and `mm_after_file_move` actions fire
- [ ] `mm_update_table_links` filter fires (allows 3rd-party table updates)

**Copy:**
- [ ] Copy mode toggle switches DnD to copy
- [ ] Physical file copied to destination (unique filename on collision)
- [ ] New WP attachment registered for the copy
- [ ] New `mm_files` row written for the copied attachment
- [ ] Original attachment unaffected

**Rename:**
- [ ] Rename button active only when exactly one file is checked
- [ ] Rename input appears; extension field is read-only
- [ ] Physical file renamed on disk
- [ ] Attachment `post_title`, `post_name`, `_wp_attached_file` updated
- [ ] All embedded links updated to new filename
- [ ] Thumbnail filenames updated on disk

**Delete:**
- [ ] Delete button active when one or more files are checked
- [ ] Confirmation prompt shown before deletion
- [ ] `wp_delete_attachment( $id, true )` called — removes from WP + disk + thumbnails
- [ ] `mm_files` row deleted
- [ ] `delete_attachment` WP action fires (hook in place)
- [ ] File removed from grid after deletion (no page reload)

---

## Phase 8 — Folder Operations

**Goal:** Create, delete, and hide folders all work. Folder refresh detects new server-side folders. Daily cron scan fires correctly.

### Files to create / modify

```
includes/
├── FileSystem/
│   └── class-folder-manager.php  ← create(), delete(), hide(), refresh(), cron scan
├── Core/
│   └── class-activator.php       ← wire cron
├── Ajax/
│   └── class-ajax-handler.php    ← mm_create_folder, mm_delete_folder, mm_hide_folder, mm_refresh_folders
└── Core/
    └── class-scheduler.php       ← mm_folder_scan cron callback
```

### Checklist

**Create folder:**
- [ ] Add Folder toolbar action prompts for folder name
- [ ] Folder name validated (no spaces; no path traversal characters)
- [ ] Physical directory created at correct path
- [ ] `mm_folder` CPT post created with correct `post_parent` and `post_name`
- [ ] `_mm_folder_path` post meta written
- [ ] Folder appears in tree immediately (no page reload)
- [ ] `mm_before_folder_create` and `mm_after_folder_create` actions fire

**Delete folder:**
- [ ] Right-click → Delete option triggers `mm_delete_folder`
- [ ] Server validates folder is empty before deleting
- [ ] If not empty: returns error message to user
- [ ] If empty: physical directory removed; CPT post deleted; folder removed from tree

**Hide folder:**
- [ ] Right-click → Hide option triggers `mm_hide_folder`
- [ ] `_mm_hidden` post meta set to `true` on CPT post
- [ ] Sentinel file written to disk (`mm-hidden` marker file)
- [ ] Folder removed from tree (no page reload)
- [ ] Folder and its sub-folders skipped during all future scans

**Refresh / Cron scan:**
- [ ] Refresh toolbar button triggers immediate `mm_refresh_folders` AJAX
- [ ] New folders found on disk imported as `mm_folder` CPT posts
- [ ] Hidden folders (sentinel file present) skipped during scan
- [ ] Daily cron `mm_folder_scan` fires `Scheduler::scan_for_new_folders()`
- [ ] Cron scan correctly imports new folders discovered since last run

---

## Phase 9 — Sync

**Goal:** Sync button imports all FTP/server-added files in the current folder into the WP Media Library. Chunked processing handles large folders without timeout.

### Files to create / modify

```
includes/
├── FileSystem/
│   └── class-sync-manager.php    ← prepare(), process_chunk()
├── Ajax/
│   └── class-ajax-handler.php    ← mm_sync_folder, mm_sync_chunk
```

### Checklist

- [ ] Sync button fires `mm_sync_folder` AJAX for current folder
- [ ] Files already in WP library are not re-imported (checked via `_wp_attached_file` lookup)
- [ ] New files are imported: `wp_insert_attachment` → `wp_generate_attachment_metadata` → `mm_files` row
- [ ] Chunked: JS loops `mm_sync_chunk` until `remaining === 0`
- [ ] Progress indicator updates after each chunk
- [ ] `.webp` files skipped when `mm_skip_webp` option is enabled
- [ ] New sub-folders found during scan are imported as `mm_folder` CPT posts
- [ ] Sync queue stored in transient (not `wp_options`)
- [ ] `mm_after_sync` action fires on completion with `$folder_id` and `$imported_count`
- [ ] Large folder (200+ files) syncs completely without PHP timeout

---

## Phase 10 — Settings Page

**Goal:** Options tab saves all settings correctly. BDA tab visible to admins only. Sort preference stored per user.

### Files to create / modify

```
includes/
├── Admin/
│   └── class-settings-page.php
├── Ajax/
│   └── class-ajax-handler.php    ← mm_save_settings
```

### Checklist

**Options tab:**
- [ ] Items per page field saves; grid respects new value
- [ ] Disable image scaling toggle saves; `big_image_size_threshold` filter applied/removed accordingly
- [ ] Skip WebP on sync toggle saves; sync respects it
- [ ] Move/Copy default toggle saves; DnD defaults to correct mode
- [ ] Editor role can access Options tab and save settings

**BDA tab:**
- [ ] BDA tab only visible/accessible to users with `manage_options`
- [ ] Editor navigating to BDA tab sees an access-denied message (not a PHP error)
- [ ] All BDA settings fields present (enable, prevent listing, prevent hotlinking, user role, no-access page)
- [ ] Saving BDA settings writes `.htaccess` to protected directory
- [ ] BDA master switch off removes `.htaccess` access restrictions

**Help:**
- [ ] Help tab present on settings page with contextual content per tab

---

## Phase 11 — Thumbnail Regeneration

**Goal:** Thumbnails page queues and processes thumbnail regeneration for all images in a selected folder. Progress reported per-file. SVGs skipped.

### Files to create / modify

```
includes/
├── Admin/
│   └── class-thumbnails-page.php
├── Thumbnails/
│   └── class-regen-manager.php
├── Ajax/
│   └── class-ajax-handler.php    ← mm_regen_thumbnails, mm_regen_process
assets/
└── js/
    ├── mm-thumbnails.js
    └── mm-thumbnails.min.js
```

### Checklist

- [ ] Thumbnails page renders folder selector dropdown
- [ ] Dropdown populated from all `mm_folder` CPT posts
- [ ] Regenerate button fires `mm_regen_thumbnails` to build queue
- [ ] Queue returned as list of attachment IDs
- [ ] JS loops `mm_regen_process` one ID at a time
- [ ] Progress bar updates after each image: "X of Y"
- [ ] SVG attachments are skipped (not counted as failed)
- [ ] Non-image attachments skipped
- [ ] On completion: success message shown
- [ ] `mm_regen_capability` filter respected (default `edit_others_posts`)

---

## Phase 12 — Block Direct Access

**Goal:** Protected directory exists. Files can be moved in/out of it. IP block list works. `.htaccess` rules written correctly. No-access redirect page works.

### Files to create / modify

```
includes/
├── Security/
│   ├── class-bda-manager.php
│   └── class-ip-blocker.php
├── Ajax/
│   └── class-ajax-handler.php    ← mm_save_bda_settings, mm_toggle_file_access,
│                                    mm_save_no_access_page, mm_get_protected_files,
│                                    mm_add_blocked_ip, mm_remove_blocked_ips, mm_get_blocked_ips
assets/
└── js/
    ├── mm-bda.js
    └── mm-bda.min.js
```

### Checklist

**Protected directory:**
- [ ] `protected-content/` directory exists under uploads after BDA is enabled
- [ ] `.htaccess` with `Deny from all` written to protected directory
- [ ] `Options -Indexes` added when "Prevent directory listing" is on
- [ ] `RewriteRule` for hotlink prevention added when "Prevent hotlinking" is on
- [ ] Direct URL to a file in protected-content returns 403

**Block/Unblock files:**
- [ ] Selecting a file and choosing Block from Bulk Actions fires `mm_toggle_file_access`
- [ ] File physically moved to `protected-content/{original-path}/`
- [ ] `mm_protected` row written
- [ ] Embedded links in posts updated to reflect new path
- [ ] Blocked file displays with visual indicator in grid (red border or badge)
- [ ] Unblocking moves file back; `mm_protected` row removed; links updated
- [ ] `wp_prepare_attachment_for_js` adds blocked status data to JS

**IP blocking:**
- [ ] IP block list displays current entries from `mm_blocked_ips`
- [ ] Add IP validates format (IPv4 or IPv6)
- [ ] Duplicate IP rejected (DB unique constraint)
- [ ] Removing IPs deletes from DB and rewrites `.htaccess`
- [ ] IPv6 addresses accepted (varchar 45 confirmed)

**No-access page:**
- [ ] Page dropdown shows all published WP pages
- [ ] Selecting and saving a page writes `mm_bda_no_access_page_id`
- [ ] Blocked IP accessing protected content is redirected to selected page

**Admin-only gate:**
- [ ] All BDA AJAX handlers verify `manage_options` before processing

---

## Phase 13 — Polish & Hardening

**Goal:** All help tabs complete. All edge cases handled. Full nonce coverage verified. Uninstall confirmed clean. Final QA pass across all features.

### Checklist

**Help tabs:**
- [x] Library page: help tab with full toolbar reference
- [x] Settings page: help tab with option descriptions per tab
- [x] Thumbnails page: help tab with regen instructions

**Security:**
- [x] Every AJAX handler has nonce check before any other logic
- [x] Every AJAX handler has capability check before processing
- [x] All `$_POST` / `$_GET` inputs sanitized (`sanitize_text_field`, `intval`, `sanitize_url`, etc.)
- [x] All output escaped (`esc_html`, `esc_attr`, `esc_url`)
- [x] Path traversal guard (`PathHelper::is_path_inside`) applied everywhere a user-supplied path is used

**Uninstall:**
- [x] `uninstall.php` drops all three tables
- [x] All `mm_*` options deleted
- [x] All `mm_folder` CPT posts deleted
- [x] `mm_sort_field` and `mm_sort_direction` user meta deleted for all users
- [x] `mm_folder_scan` cron cleared
- [x] Protected content `.htaccess` removed via `BdaManager::unprotect_folder()` during uninstall

**Compatibility:**
- [x] No PHP errors on WP latest (current)
- [x] No JS console errors on Library, Settings, Thumbnails pages
- [x] Plugin activates cleanly alongside Kaneism child theme with no conflicts
- [x] Old `media-library-plus` plugin deactivated before Media Manager activated

**Final smoke test:**
- [x] Create folder → upload files → move file → rename file → delete file — full cycle works
- [x] Sync: FTP a file to a folder, sync, file appears in grid
- [x] Thumbnail regen: select folder, regenerate, progress completes, thumbs updated
- [x] BDA: enable, block a file, verify 403, unblock, verify accessible again
- [x] Settings: change items-per-page, verify grid respects it
- [x] Editor account: full library access confirmed; BDA settings inaccessible confirmed
- [x] WP media modal: "Media Folders" tab appears in Add Media (post/page editor)
- [x] WP media modal: "Media Folders" tab appears in Featured Image meta box
- [x] Selecting a file from the Media Folders tab correctly inserts into post / sets featured image
