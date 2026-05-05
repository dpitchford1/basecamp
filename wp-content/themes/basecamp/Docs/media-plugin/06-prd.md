# Media Manager — Product Requirements Document

**Document:** `06-prd.md`  
**Status:** 🟡 Draft  
**Last updated:** 2026-05-04  
**Depends on:** `01-feature-inventory.md`, `02-admin-audit.md`, `03-database-audit.md`, `04-hook-inventory.md`, `05-dependencies.md`

---

## 1. Project Summary

A bespoke WordPress plugin for managing the physical folder structure of the WordPress Media Library. Built as a clean-room rebuild of the vendor plugin `media-library-plus` (Max Foundry, v8.3.3), stripped to exactly what the site needs, fully namespaced and modular, and designed to run on the Basecamp/Kaneism stack without legacy scaffolding, upsell code, or external plugin dependencies.

---

## 2. Naming & Slugs

| Thing | Value |
|---|---|
| Plugin name | Media Manager |
| Plugin slug | `media-manager` |
| PHP namespace root | `MediaManager\` |
| Text domain | `media-manager` |
| CPT: folder | `mm_folder` |
| DB option prefix | `mm_` |
| DB table: file–folder map | `{prefix}mm_files` |
| DB table: protected files | `{prefix}mm_protected` |
| DB table: blocked IPs | `{prefix}mm_blocked_ips` |
| AJAX action prefix | `mm_` |
| Nonce key | `mm_nonce` |
| Cron hook | `mm_folder_scan` |

---

## 3. Scope

### In scope

| Area | Detail |
|---|---|
| Folder management | Create, delete, hide physical folders on disk |
| File operations | Upload, move, copy, rename, delete |
| Sync | Scan folder for FTP/server-added files and import into WP |
| Display & sorting | File grid, sort by date or title, ASC/DESC, configurable items per page |
| Thumbnail regeneration | Bulk regen for selected files; chunked AJAX processing |
| Block Direct Access | Protected directory, IP blocking, directory listing prevention, hotlink prevention, no-access redirect page |
| Settings | Items per page, disable image scaling, skip WebP on sync, move/copy default, BDA options |
| Role access | Administrators (full) + Editors (library access); Authors (upload only via native WP); Subscribers (none) |

### Explicitly out of scope (v1)

| Feature | Reason |
|---|---|
| Frontend gallery / shortcodes | Requires separate UX/workflow planning; deferred |
| Download links with expiry/count limits | Not needed |
| Auto-protect new uploads | Not needed |
| Base64 image proxy on frontend | Performance-hostile; dropped |
| Right-click disable on images | Easily bypassed; dropped |
| MaxGalleria integration | External dependency; dropped |
| NextGen Gallery integration | External dependency; dropped |
| Image SEO bulk tool | Not needed |
| Support / FAQ / system info admin pages | Vendor scaffolding; dropped |
| Upsell notices and banners | Vendor promotional code; dropped |
| DB migration from old plugin | Clean slate install |
| Multisite | Not applicable |

---

## 4. Data Model

### 4a. Custom Post Type — `mm_folder`

Each physical folder on disk is represented as a CPT post. The WP hierarchy (`post_parent`) encodes the folder tree. `post_name` stores the folder's physical directory name (slug).

| Property | Value |
|---|---|
| Post type slug | `mm_folder` |
| Public | `false` |
| Hierarchical | `true` |
| Show in UI | `false` |
| Show in menu | `false` |
| Show in admin bar | `false` |
| Exclude from search | `true` |
| Supports | `false` (title stored as `post_name`) |

**Post meta (on `mm_folder` posts):**

| Key | Type | Description |
|---|---|---|
| `_mm_folder_path` | string | Absolute server path to the folder |
| `_mm_hidden` | bool | Whether the folder is hidden from the tree |

---

### 4b. Custom Tables

#### `{prefix}mm_files`

Maps each WP attachment to its folder. One row per attachment.

```sql
CREATE TABLE {prefix}mm_files (
  attachment_id bigint(20)  NOT NULL,
  folder_id     bigint(20)  NOT NULL,
  PRIMARY KEY (attachment_id),
  KEY folder_id (folder_id)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

> Note: old table (`mgmlp_folders`) had no index on `folder_id`. Added in rebuild for reverse lookups (all files in a folder).

---

#### `{prefix}mm_protected`

Tracks which files are in the protected directory.

```sql
CREATE TABLE {prefix}mm_protected (
  attachment_id bigint(20)  NOT NULL,
  blocked       tinyint(1)  NOT NULL DEFAULT 0,
  protected_at  datetime    NOT NULL,
  PRIMARY KEY (attachment_id)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

#### `{prefix}mm_blocked_ips`

IP addresses blocked from accessing the protected content directory.

```sql
CREATE TABLE {prefix}mm_blocked_ips (
  ip_id   bigint(20)  NOT NULL AUTO_INCREMENT,
  address varchar(45) NOT NULL,
  PRIMARY KEY (ip_id),
  UNIQUE KEY address (address)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

> `varchar(45)` supports IPv6. Old table used `varchar(16)`. Added `UNIQUE` constraint to prevent duplicates.

---

### 4c. `wp_options` Keys

All options stored under an `mm_` prefix. Single settings array where possible.

| Option Key | Type | Default | Notes |
|---|---|---|---|
| `mm_version` | string | — | Plugin version; used for DB upgrade checks |
| `mm_upload_folder_name` | string | `uploads` | Name of the WP uploads root folder |
| `mm_upload_folder_id` | int | `0` | CPT post ID of the uploads root folder |
| `mm_items_per_page` | int | `500` | Files shown per page in library grid |
| `mm_move_or_copy` | string | `move` | Default drag-and-drop action |
| `mm_disable_scaling` | bool | `false` | Disable WP large image scaling |
| `mm_skip_webp` | bool | `false` | Skip `.webp` files during sync |
| `mm_bda_enabled` | bool | `false` | Block Direct Access master switch |
| `mm_bda_prevent_listing` | bool | `false` | Prevent directory listing |
| `mm_bda_prevent_hotlinking` | bool | `false` | Prevent image hotlinking |
| `mm_bda_user_role` | string | `admins` | Who can view protected files: `admins` or `authors` |
| `mm_bda_no_access_page_id` | int | `0` | WP page ID for no-access redirect |

**Not stored in `wp_options`:**
- Sort preference (field + direction) → **user meta** (`mm_sort_field`, `mm_sort_direction`) per user
- Sync process state → **transients** (`mm_sync_queue`, `mm_sync_folder_id`), TTL 1 hour

---

### 4d. User Meta Keys

| Key | Type | Default | Notes |
|---|---|---|---|
| `mm_sort_field` | string | `date` | `date` or `title` |
| `mm_sort_direction` | string | `ASC` | `ASC` or `DESC` |

---

### 4e. Scheduled Events

| Cron Hook | Frequency | Callback | Purpose |
|---|---|---|---|
| `mm_folder_scan` | Daily | `MediaManager\Core\Scheduler::scan_for_new_folders` | Detect new server-side folders and import them |

---

## 5. Admin UI

### 5a. Menu Structure

Top-level admin menu item **Media Manager**, containing:

```
Media Manager
├── Library          (slug: mm-library)   ← default page
├── Settings         (slug: mm-settings)
└── Thumbnails       (slug: mm-thumbnails)
```

Capability required to see menu: `edit_others_posts` (Editors and above).  
BDA settings tab: `manage_options` only.

---

### 5b. Library Page (`mm-library`)

The main admin screen. Two-column layout.

**Left column — Folder Tree**

- jsTree component displaying the full physical folder hierarchy
- Root: the WP uploads directory
- Each node is an `mm_folder` CPT post
- Click → loads folder contents into right panel via AJAX
- Right-click context menu:
  - Hide folder (sets `_mm_hidden` flag; removes from tree; writes sentinel file to disk)
  - Delete folder (only if empty; server-side validation)
- Refresh icon triggers immediate folder scan

**Right column — Toolbar + File Grid**

Toolbar icons (left to right):

| Action | Behaviour |
|---|---|
| Add Folder | Opens inline name input; creates physical dir + CPT post on submit |
| Upload Files | Toggles drag-and-drop upload panel |
| Refresh Folders | AJAX: scan for new folders, rebuild tree |
| Move mode | Sets DnD action to move (default) |
| Copy mode | Sets DnD action to copy |
| Sort by Date | Reloads grid sorted by upload date |
| Sort by Title | Reloads grid sorted by filename |
| Reverse Order | Toggles ASC/DESC |
| Sync | Scans current folder for new files; imports to WP |
| Rename | Renames the (single) selected file |
| Regenerate Thumbnails | Queues regen for selected files |
| Delete | Deletes selected files from WP + disk |

File grid:

- Thumbnail per file (icon for non-image types)
- Checkbox per file; shift-click range select; Select All checkbox
- Drag file thumbnail onto folder tree node → move or copy
- Click thumbnail → opens native WP edit-attachment in new tab
- Bulk Actions dropdown:
  - Block / Unblock file (BDA — toggle protected status)

Search bar:

- Text input + search button
- Searches file and folder names
- Results page; clicking a result navigates to that folder

Upload panel (shown/hidden by toolbar toggle):

- Drag-and-drop zone for multi-file upload
- File browse button for single-file upload
- Upload targets the currently selected folder

---

### 5c. Settings Page (`mm-settings`)

Two tabs, both under one admin page.

**Tab: Options** (visible to Editors and Admins)

| Setting | Control | Default |
|---|---|---|
| Number of files to display | Number input | 500 |
| Disable large image scaling | Checkbox | Off |
| Skip .webp files when syncing | Checkbox | Off |
| Default drag-and-drop action | Radio: Move / Copy | Move |

**Tab: Block Direct Access** (Admins only — redirect Editors away from this tab)

| Setting | Control | Default |
|---|---|---|
| Enable Block Direct Access | Toggle | Off |
| Prevent directory listing | Toggle | Off |
| Prevent hotlinking | Toggle | Off |
| User roles who can view protected files | Select: Admins / File Authors | Admins |
| No-access redirect page | Page dropdown | — (none) |
| IP Block List | Textarea + Add/Remove controls | empty |

---

### 5d. Thumbnails Page (`mm-thumbnails`)

| Element | Behaviour |
|---|---|
| Folder selector | Dropdown of all `mm_folder` posts |
| Regenerate button | Kicks off chunked AJAX regen for all images in selected folder |
| Progress bar | Per-file progress; count of processed / total |
| Skip logic | SVGs automatically excluded; non-image attachments skipped |

---

### 5e. Help

Use WordPress native `add_help_tab()` API on each screen object. One help tab per page with contextual content. No custom slide-out panel.

---

## 6. File Operations — Detailed Behaviours

### 6a. Upload

1. User selects a folder in the tree
2. Opens upload panel; selects or drops files
3. AJAX handler receives file, validates nonce + `upload_files` capability
4. `wp_handle_upload()` writes file to the selected folder's physical path
5. `wp_insert_attachment()` registers the file as a WP attachment
6. `wp_generate_attachment_metadata()` generates thumbnails
7. `{prefix}mm_files` row inserted: `attachment_id` → `folder_id`
8. If BDA auto-protect is off (always in v1): file lands in normal uploads

### 6b. Move

1. User drags file thumbnail onto a folder tree node (move mode)
2. AJAX: nonce + `edit_others_posts` capability validated
3. Physical file moved to target folder path
4. All thumbnail files moved to match
5. `_wp_attached_file` post meta updated on the attachment
6. All embedded links in `wp_posts.post_content` updated (search + replace)
7. `{prefix}mm_files` row updated with new `folder_id`
8. `mlfp_filter_update_tables_links` / `mlfp_filter_update_tables_fields` filters fired (renamed `mm_update_table_links` / `mm_update_table_fields`) so third-party code can register additional tables to update

### 6c. Copy

1. Same drag, but copy mode is active
2. Physical file copied to target folder (new filename if collision)
3. New WP attachment registered for the copy
4. `{prefix}mm_files` row inserted for new attachment
5. Embedded links **not** updated (copy is additive)

### 6d. Rename

1. User checks one file checkbox; clicks Rename toolbar button
2. Inline name input appears (extension locked)
3. AJAX: physical file renamed, WP attachment `post_title` + `post_name` + `_wp_attached_file` updated
4. All embedded links updated to new filename

### 6e. Delete

1. User selects one or more files; clicks Delete
2. Confirmation prompt
3. AJAX: `wp_delete_attachment( $id, true )` — deletes from WP + disk (including thumbnails)
4. `{prefix}mm_files` row deleted

### 6f. Sync

1. User clicks Sync toolbar button while a folder is selected
2. AJAX reads all physical files in that folder directory
3. Each file not already in WP library is imported: `wp_insert_attachment()` → `wp_generate_attachment_metadata()` → row in `{prefix}mm_files`
4. WebP files are skipped if `mm_skip_webp` is enabled
5. New sub-folders found during scan are registered as `mm_folder` CPT posts

---

## 7. Block Direct Access (BDA)

### 7a. Protected Directory

- Physical path: `{uploads_basedir}/protected-content/`
- `.htaccess` written on BDA enable to deny direct web access
- `Options -Indexes` written if "Prevent directory listing" is on
- `RewriteRule` written if "Prevent hotlinking" is on
- When a file is blocked: it is physically moved to `protected-content/{original-path}/`; a row is written to `{prefix}mm_protected`
- When unblocked: moved back; row removed from `{prefix}mm_protected`
- Embedded links in posts updated on both block and unblock (same mechanism as Move)

### 7b. IP Blocking

- Admin can add/remove IPv4 and IPv6 addresses from the block list
- Blocked IPs stored in `{prefix}mm_blocked_ips`
- Block logic applied via `.htaccess` `Deny from` rules written on save
- No-access page redirect: if set, blocked IPs are redirected to the chosen WP page

### 7c. File Access for Protected Images

- Protected images are not accessible via direct URL
- In WP admin media grid: blocked files get a visual indicator (CSS class via `wp_prepare_attachment_for_js`)
- Protected files still appear in the admin library view with a "blocked" badge

---

## 8. Hooks & Extensibility

### 8a. Actions (exposed for extensibility)

| Hook | When fired | Args |
|---|---|---|
| `mm_before_folder_create` | Before physical dir creation | `$folder_name`, `$parent_id` |
| `mm_after_folder_create` | After folder + CPT post created | `$folder_id`, `$folder_path` |
| `mm_before_file_move` | Before a file is moved | `$attachment_id`, `$source_folder_id`, `$dest_folder_id` |
| `mm_after_file_move` | After a file is moved + links updated | `$attachment_id`, `$dest_folder_id` |
| `mm_before_file_delete` | Before file deletion | `$attachment_id` |
| `mm_after_sync` | After a folder sync completes | `$folder_id`, `$imported_count` |

### 8b. Filters (exposed for extensibility)

| Hook | Purpose | Args |
|---|---|---|
| `mm_post_type_args` | Filter `mm_folder` CPT registration args | `$args` |
| `mm_toolbar_buttons` | Add custom buttons to the library toolbar | `$buttons` |
| `mm_update_table_links` | Register extra DB tables whose link fields should update on file move | `$tables` |
| `mm_update_table_fields` | Register extra DB fields to update on file move | `$fields` |
| `mm_items_per_page` | Override items-per-page count | `$count`, `$user_id` |
| `mm_regen_capability` | Override capability required to regenerate thumbnails | `$capability` |
| `mm_sync_skip_file` | Return `true` to skip a file during sync | `$skip`, `$file_path`, `$folder_id` |

---

## 9. Capability Matrix

| Action | Minimum Capability | WP Role |
|---|---|---|
| View library, browse folders | `edit_others_posts` | Editor+ |
| Upload files | `upload_files` | Author+ (but Media Manager UI: Editor+) |
| Move / copy / rename files | `edit_others_posts` | Editor+ |
| Delete files | `edit_others_posts` | Editor+ |
| Create / delete folders | `edit_others_posts` | Editor+ |
| Sync folder | `edit_others_posts` | Editor+ |
| Regenerate thumbnails | `edit_others_posts` (filterable via `mm_regen_capability`) | Editor+ |
| View / change Options settings | `edit_others_posts` | Editor+ |
| View / change BDA settings | `manage_options` | Admin only |
| Manage IP block list | `manage_options` | Admin only |
| Block / unblock files | `manage_options` | Admin only |

---

## 10. JavaScript Strategy

- All admin JS written in vanilla JS (ES6+) or minimal jQuery where WP already loads it
- No bundler required for v1 — single compiled/concatenated file per admin page
- jsTree retained for folder tree (evaluate replacement post-v1)
- Select2 dropped — native `<select>` for the no-access page picker
- Font Awesome: evaluate whether Basecamp/Kaneism admin loads it globally; if yes, reuse; if no, bundle only the ~10 icons needed (SVG sprite)
- AJAX calls use `wp_localize_script` to pass `ajaxurl` and nonce

---

## 11. Non-Functional Requirements

| Requirement | Detail |
|---|---|
| No external HTTP requests | Plugin makes zero remote calls at any time |
| No Composer in production | All PHP hand-written and namespaced |
| No Gutenberg blocks | Admin UI only; no block editor integration |
| No frontend output | No scripts or styles enqueued on the frontend (except BDA `.htaccess` rules) |
| Nonce validation | Every AJAX handler verifies nonce before processing |
| Input sanitization | All `$_POST` / `$_GET` inputs sanitized before use; all output escaped |
| Uninstall cleanup | `uninstall.php` drops all three custom tables, deletes all `mm_*` options, removes scheduled events |
| Activation safety | `dbDelta()` used for table creation — safe to run on re-activation without data loss |
