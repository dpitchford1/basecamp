# Media Manager — Database Audit

**Status:** ✅ Complete  
**Last updated:** 2026-05-04  
**Source audited:** `media-library-plus.php` (activation methods, constants, `setup_hooks`)

---

## 1. Custom Post Type

| Property | Value |
|---|---|
| Post type slug | `mgmlp_media_folder` |
| Constant | `MAXGALLERIA_MEDIA_LIBRARY_POST_TYPE` |
| Label | Media Folder |
| Public | `false` |
| Hierarchical | `true` (supports parent-child folder nesting) |
| `show_in_menu` | `false` |
| `show_in_admin_bar` | `false` |
| `exclude_from_search` | `true` |
| `supports` | `false` (no title, editor, etc.) |
| Purpose | Each folder is a CPT post. `post_parent` encodes the folder hierarchy. `post_name` stores the folder slug / physical directory name. |

**Rebuild note:** Keep the CPT approach — it's the right WP-idiomatic model for hierarchical folder nodes. Rename slug to `media_manager_folder` in the new plugin.

---

## 2. Custom Database Tables

Three custom tables are created on activation via `dbDelta()`.

### 2a. `{prefix}mgmlp_folders`

Maps each attachment (media file) to its folder.

```sql
CREATE TABLE IF NOT EXISTS {prefix}mgmlp_folders (
  post_id   bigint(20) NOT NULL,   -- WP attachment post ID
  folder_id bigint(20) NOT NULL,   -- mgmlp_media_folder post ID
  PRIMARY KEY (post_id)
) DEFAULT CHARSET=utf8;
```

| Column | Type | Notes |
|---|---|---|
| `post_id` | `bigint(20)` | WP attachment ID; PK — one folder per file |
| `folder_id` | `bigint(20)` | `mgmlp_media_folder` CPT post ID |

**Rebuild note:** Keep this table. Rename to `{prefix}media_manager_files` in new plugin. Consider adding a `folder_id` index for reverse lookups (old table lacks this).

---

### 2b. `{prefix}mgmlp_block_access`

Tracks protected file state and download limits (partial keep — limit/expiry columns dropped).

```sql
CREATE TABLE IF NOT EXISTS {prefix}mgmlp_block_access (
  attachment_id   bigint(20)     NOT NULL,
  hash_id         varchar(256)   NULL,
  time            datetime       NULL,
  block           tinyint(4)     NULL,   -- 1 = blocked, 0 = not blocked
  count           mediumint(9)   NULL,   -- download count (DROP)
  download_limit  mediumint(9)   NULL,   -- max downloads allowed (DROP)
  expiration_date date           NULL,   -- expiry (DROP)
  PRIMARY KEY (attachment_id)
) DEFAULT CHARSET=utf8;
```

**Rebuild note:** Keep the concept but simplify the schema. New table `{prefix}media_manager_protected`:

```sql
CREATE TABLE {prefix}media_manager_protected (
  attachment_id bigint(20) NOT NULL,
  blocked       tinyint(1) NOT NULL DEFAULT 0,
  protected_at  datetime   NOT NULL,
  PRIMARY KEY (attachment_id)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Drop `hash_id`, `count`, `download_limit`, `expiration_date` — all tied to the download link feature which is cut.

---

### 2c. `{prefix}mgmlp_blocked_ips`

IP addresses blocked from accessing protected download links.

```sql
CREATE TABLE IF NOT EXISTS {prefix}mgmlp_blocked_ips (
  ip_id   bigint(20)  NOT NULL AUTO_INCREMENT,
  address varchar(16) NOT NULL,
  PRIMARY KEY (ip_id)
) DEFAULT CHARSET=utf8;
```

**Rebuild note:** Keep. Rename to `{prefix}media_manager_blocked_ips`. Expand `address` to `varchar(45)` to support IPv6.

---

## 3. `wp_options` Keys

All option keys registered or read by the current plugin. Grouped by area.

### 3a. Core / Folder State

| Option Key | Constant | Type | Default | Keep? |
|---|---|---|---|---|
| `mgmlp_upload_folder_name` | `MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_NAME` | string | `"uploads"` | ✅ Keep |
| `mgmlp_upload_folder_id` | `MAXGALLERIA_MEDIA_LIBRARY_UPLOAD_FOLDER_ID` | int | `0` | ✅ Keep |
| `maxgalleria_media_library_version` | `MAXGALLERIA_MEDIA_LIBRARY_VERSION_KEY` | string | — | ✅ Keep (rename key) |

### 3b. Display & Sorting

| Option Key | Constant | Type | Default | Keep? |
|---|---|---|---|---|
| `mlf_items_per_page` | `MAXGALLERIA_MLP_ITEMS_PRE_PAGE` | int | `500` | ✅ Keep |
| `mgmlp_sort_order` | `MAXGALLERIA_MEDIA_LIBRARY_SORT_ORDER` | int | `0` | 🔄 Redesign — user meta |
| `mlf_sort_order_type` | `MAXGALLERIA_MLF_SORT_TYPE` | ASC/DESC | `ASC` | 🔄 Redesign — user meta |
| `mgmlp_move_or_copy` | `MAXGALLERIA_MEDIA_LIBRARY_MOVE_OR_COPY` | on/off | `on` | ✅ Keep |

### 3c. Image Processing

| Option Key | Constant | Type | Default | Keep? |
|---|---|---|---|---|
| `mlfp_disable_scaling` | `MAXGALLERIA_DISABLE_SCALLING` | on/off | `off` | ✅ Keep |
| `mlfp-skip-webp-files` | `MLFP_SKIP_WEBP_FILES` | on/off | `off` | ✅ Keep |

### 3d. Sync State (transient-like, stored in options)

| Option Key | Constant | Purpose | Keep? |
|---|---|---|---|
| `mlfp_sync_folder_path` | `MAXG_SYNC_FOLDER_PATH` | Current sync folder path | 🔄 Redesign — use transient |
| `mlfp_sync_folder_path_id` | `MAXG_SYNC_FOLDER_PATH_ID` | Current sync folder ID | 🔄 Redesign — use transient |
| `mlfp_sync_files` | `MAXG_SYNC_FILES` | Files pending sync | 🔄 Redesign — use transient |
| `mlfp_sync_folders` | `MAXG_SYNC_FOLDERS` | Folders pending sync | 🔄 Redesign — use transient |
| `mlfp_move_file_ids` | `MAXG_MC_FILES` | Move/copy queue IDs | 🔄 Redesign — use transient |
| `mlfp_move_file_destination` | `MAXG_MC_DESTINATION_FOLDER` | Move/copy destination | 🔄 Redesign — use transient |

### 3e. Block Direct Access (BDA)

| Option Key | Constant | Type | Default | Keep? |
|---|---|---|---|---|
| `mlfp-bda` | `MLFP_BDA` | on/off | `off` | ✅ Keep |
| `mlfp-bda-dir-listing` | `MLFP_BDA_DIR_LISTING` | on/off | `off` | ✅ Keep |
| `mlfp-bda-hotlinking` | `MLFP_BDA_HOTLINKING` | on/off | `off` | ✅ Keep |
| `mlfp-bda-user-role` | `MLFP_BDA_USER_ROLE` | admins/authors | `admins` | ✅ Keep |
| `mlfp-no-access-page-id` | `MLFP_NO_ACCESS_PAGE_ID` | int | `0` | ✅ Keep |
| `mlfp-no-access-page-id-title` | `MLFP_NO_ACCESS_PAGE_TITLE` | string | `''` | ✅ Keep |
| `mlfp-bda-auto-protect` | `MLFP_BDA_AUTO_PROTECT` | on/off | `off` | ❌ Drop |
| `mlfp-bda-display-fe-images` | `MLFP_BDA_DISPLAY_FE_IMAGES` | on/off | `off` | ❌ Drop |
| `mlfp-bda-prevent-right-click` | `MLFP_BDA_PREVENT_RIGHT_CLICK` | on/off | `off` | ❌ Drop |
| `mlfp-bda-auto-protect-disabled` | `MLFP_BDA_AUTO_PROTECT_DISABLED` | on/off | — | ❌ Drop |
| `mlfp-download-page` | `MLFP_BDA_DOWNLOAD_PAGE` | int | — | ❌ Drop |
| `mlfp-bda-media` | `MLFP_BDA_MEDIA` | — | — | ❌ Drop |

### 3f. Notices / Upsell (All Drop)

| Option Key | Constant | Purpose |
|---|---|---|
| `maxgalleria_media_library_ignore_notice` | `MAXGALLERIA_MEDIA_LIBRARY_IGNORE_NOTICE` | Dismiss admin notice |
| `maxgalleria_mlp_review_notice` | `MAXGALLERIA_MLP_REVIEW_NOTICE` | wp.org review nag |
| `maxgalleria_mlp_feature_notice` | `MAXGALLERIA_MLP_FEATURE_NOTICE` | Feature/update nag |
| `mlf_display_info` | `MAXGALLERIA_MLP_DISPLAY_INFO` | Info banner display flag |
| `mlf_disable_ft` | `MAXGALLERIA_MLP_DISABLE_FT` | Disable feature tour |

### 3g. Misc / Performance

| Option Key | Constant | Type | Keep? |
|---|---|---|---|
| `mgmlp-index` | `MAXGALLERIA_POSTMETA_INDEX` | on/off | 🔄 Redesign — handle at activation |
| `mgmlp_src_fix` | `MAXGALLERIA_MEDIA_LIBRARY_SRC_FIX` | bool | ❌ Drop — legacy fix flag |

---

## 4. Post Meta Keys

| Meta Key | Post Type | Purpose | Keep? |
|---|---|---|---|
| `_wp_attached_file` | `attachment` | Standard WP meta — used in lookups by `attachments.php` | n/a (WP core) |

> The old plugin does not write any custom post meta of its own to attachments. Folder membership is tracked in the custom `mgmlp_folders` table, not post meta.

---

## 5. User Meta Keys

| Meta Key | Purpose | Keep? |
|---|---|---|
| `maxgalleria_mlp_feature_notice` | Stores date after which review/feature notice is shown | ❌ Drop — upsell scaffolding |

**Rebuild note:** Sort preference (field + direction) should be stored in user meta in the new plugin so it's per-user rather than global.

---

## 6. Scheduled Events

| Event Hook | Frequency | Callback | Keep? |
|---|---|---|---|
| `new_folder_check` | Daily | `admin_check_for_new_folders` | ✅ Keep — scans for new server-side folders |

**Rebuild note:** Register with `wp_schedule_event` on activation, clear on deactivation. Keep daily frequency (can make configurable later).

---

## 7. Filters for DB Table/Field Extensibility

These constants are defined but only lightly used — they expose filters for third-party code to add update targets when files are moved:

| Constant | Filter Hook | Purpose | Keep? |
|---|---|---|---|
| `MGMLP_FILTER_SET_UPDATE_TABLE_LINKS` | `mlfp_filter_update_tables_links` | Let 3rd parties register extra DB tables to update on file move | 🔄 Redesign — keep the concept, clean up implementation |
| `MGMLP_FILTER_SET_UPDATE_TABLE_FIELDS` | `mlfp_filter_update_tables_fields` | Let 3rd parties register extra DB fields to update | 🔄 Redesign |
