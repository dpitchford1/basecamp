# Media Manager

**Version:** 1.1.0  
**Requires:** WordPress 6.0+, PHP 8.0+  
**Tested up to:** WordPress 6.9  
**Text domain:** `media-manager`

Physical folder management for the WordPress Media Library. Organises uploads into real server directories, provides bulk operations, thumbnail regeneration, folder-level direct-access blocking, and IP-based blocking — all without touching core WordPress files.

---

## Features

- **Folder tree** — create, hide, and delete physical upload folders via a jsTree sidebar
- **Folder hover preview** — hover any folder in the tree to see a 4-image thumbnail strip without leaving the current folder
- **File grid** — paginated, sortable file grid per folder (sort by date or title, ascending/descending)
- **Numbered pagination** — page navigation with ellipsis condensation; sort and filter modes each preserve their own pagination context
- **Recent uploads filter** — "Last 7 days" / "Last 30 days" quick-filter buttons show recent uploads across all folders without selecting a specific folder
- **Unassigned files** — a virtual "Unassigned" node at the bottom of the tree surfaces every attachment not mapped to any folder
- **Drag-and-drop move** — drag one or more file cards onto any folder in the tree to move them; collision-safe (renames on conflict)
- **Upload** — drag-and-drop or file-picker upload directly into the active folder
- **Rename** — rename any file; updates the physical file, attachment post title, and all post content references
- **Delete** — delete files from disk and the media library in bulk
- **Sync** — detect files added to a folder outside WordPress (FTP/SFTP) and register them as attachments
- **Thumbnail regeneration** — per-folder or site-wide thumbnail regeneration with a live progress bar
- **EXIF strip** — optionally strip EXIF metadata (GPS coordinates, camera model, etc.) from JPEG uploads server-side using PHP's Imagick or GD
- **Block Direct Access (BDA)** — writes Apache `.htaccess` rules to block direct URL access to files in protected folders
- **IP blocking** — deny specific IP addresses from all media access; block list is object-cache backed for zero per-request DB queries
- **Native WP image editing** — clicking an image opens the standard WordPress media modal (crop, rotate, flip, alt text, captions) via AJAX

---

## Requirements

| Requirement | Minimum |
|---|---|
| WordPress | 6.0 |
| PHP | 8.0 |
| MySQL | 5.7 / MariaDB 10.3 |
| Web server | Apache (for BDA `.htaccess` features) |

> **BDA note:** The Block Direct Access feature writes Apache `.htaccess` rules. Nginx installations will not benefit from BDA — equivalent rules must be added manually to the Nginx config.

---

## Installation

1. Upload the `media-manager` folder to `/wp-content/plugins/`
2. Activate the plugin via **Plugins → Installed Plugins**
3. On first activation the plugin will:
   - Create three database tables (`mm_files`, `mm_protected`, `mm_blocked_ips`)
   - Walk the existing uploads directory and register all attachment → folder mappings
   - Schedule a daily folder-scan cron event
4. Navigate to **Media Manager → Library** in the WordPress admin

---

## Admin Pages

### Library
The main interface. A two-pane layout: folder tree on the left, file grid on the right.

**Toolbar actions:**
| Control | Description |
|---|---|
| New Folder | Create a subfolder inside the selected folder |
| Refresh Tree | Reload the folder tree from the server |
| Sync | Register any files added to the folder outside WordPress |
| Sort | Sort the grid by Date or Title, ascending or descending |
| Recent filter | Show uploads from the last 7 or 30 days across all folders |
| Select All | Toggle selection of all visible files on the current page |
| Bulk Delete | Delete all selected files from disk and the media library |
| Bulk Rename | Rename a single selected file (extension preserved) |
| Bulk Move | Move selected files to a chosen folder |
| Upload | Upload files into the active folder |
| Pagination | Numbered page navigation (prev / numbered pages / next) |

**Folder context menu (right-click a folder):**
- Create subfolder
- Delete folder (must be empty)
- Hide folder from tree
- Protect folder (BDA) / Remove protection

**Folder hover preview:**  
Hovering a folder node for 350 ms triggers an AJAX request for up to 4 thumbnail images from that folder. The preview strip appears to the right of the tree and disappears when the cursor leaves the tree pane.

**Unassigned files:**  
The "Unassigned" virtual node at the bottom of the tree lists every attachment in the media library that has no folder mapping. Files here can be selected and bulk-moved into a folder. The node cannot be renamed, deleted, or used as a drag-drop target.

**File cards:**
- Click thumbnail → opens native WP media modal for editing (images only; non-images open the WP attachment edit screen in a new tab)
- Checkbox → select for bulk operations
- Drag → drag one or more selected cards onto a tree folder to move them

---

### Settings
**General**
- **Files per page** — how many files to show per page in the grid (1–2000, default 50)

**Image Upload**
- **Disable big image scaling** — prevent WordPress from down-sampling images larger than 2560px on upload
- **Skip WebP generation** — do not generate `.webp` variants on upload (WordPress 5.8+)
- **Strip EXIF data** — remove all EXIF metadata (GPS coordinates, camera info, timestamps) from JPEG uploads. Requires PHP's Imagick or GD extension

---

### Thumbnails
Regenerate image thumbnails per folder or across the entire library.

1. Select a folder from the dropdown (or choose "All folders")
2. Click **Regenerate Thumbnails**
3. A progress bar shows images processed / remaining

---

### Security
Controls two independent access-restriction features.

**Block Direct Access (BDA)**
| Option | Description |
|---|---|
| Enable BDA | Activate `.htaccess`-based folder protection |
| Prevent directory listing | Adds `Options -Indexes` to protected `.htaccess` files |
| Prevent hotlinking | Adds a `RewriteCond` on Referer to block external embedding |
| Redirect blocked users to | Choose a page to redirect to, or leave blank for a raw 403 |

Folders are protected individually via the right-click context menu in the Library. The **Protected Folders** table on this page is a read-only audit list.

**IP Address Blocking**
| Option | Description |
|---|---|
| Enable IP blocking | Activate per-IP denial |
| Add IP address | Block an IPv4 or IPv6 address |
| Remove selected | Bulk-remove IPs from the block list |

> Blocked IPs are stored in the `mm_blocked_ips` table and cached in the WordPress object cache. With a persistent cache (Redis/Memcached) the DB is queried only on writes, not on every page load.

---

## Database Tables

| Table | Purpose |
|---|---|
| `{prefix}mm_files` | Maps each attachment ID to its folder ID |
| `{prefix}mm_protected` | Tracks which folders have active BDA `.htaccess` rules |
| `{prefix}mm_blocked_ips` | IP addresses denied media access |

All tables are dropped on plugin deletion (uninstall).

---

## Options

All options are autoloaded — they are fetched in WordPress's single bulk options query at boot and held in memory for the request lifetime.

| Option | Default | Description |
|---|---|---|
| `mm_items_per_page` | `50` | Files shown per page in the grid |
| `mm_disable_scaling` | `false` | Disable WP big image scaling on upload |
| `mm_skip_webp` | `false` | Skip WebP generation on upload |
| `mm_strip_exif` | `false` | Strip EXIF metadata from JPEG uploads |
| `mm_bda_enabled` | `false` | Enable Block Direct Access |
| `mm_bda_prevent_listing` | `false` | Add `Options -Indexes` to protected folders |
| `mm_bda_prevent_hotlinking` | `false` | Add Referer-based hotlink protection |
| `mm_bda_no_access_page_id` | `0` | Page to redirect blocked users to (0 = 403) |
| `mm_ip_blocking_enabled` | `false` | Enable IP address blocking |
| `mm_upload_folder_id` | `0` | Post ID of the root uploads folder |

---

## Custom Post Type

**`mm_folder`** — Each physical folder is represented as a CPT post. The post title is the folder name; `_mm_folder_path` post meta stores the absolute server path; `_mm_folder_parent` stores the parent folder post ID.

---

## Performance

- All plugin options are **autoloaded** — zero per-option DB queries on the frontend
- The IP block list is served from the **WordPress object cache** — with Redis or Memcached, no DB queries occur during IP checks under any traffic load
- `mm_files` table has a `KEY folder_id` index for fast per-folder lookups
- `mm_blocked_ips` has a `UNIQUE KEY ip_address` index (O(log n) lookup)
- File grid queries use a direct SQL JOIN on `wp_posts` rather than `WP_Query` to avoid post meta overhead

---

## Uninstall

Deleting the plugin via **Plugins → Delete** triggers a full cleanup:

1. Drops `mm_files`, `mm_protected`, `mm_blocked_ips` tables
2. Deletes all `mm_*` options
3. Removes `.htaccess` blocks from any previously protected folders
4. Deletes all `mm_folder` CPT posts and their meta
5. Clears all plugin-specific user meta
6. Unschedules the `mm_folder_scan` cron event

> Attachment posts and their physical files are **not** deleted — only the plugin's own tracking data is removed.

---

## Capability Gates

| Page / Action | Minimum capability |
|---|---|
| Library (view, bulk-select, sort, filter) | `edit_others_posts` (Editor+) |
| Upload | `upload_files` (Author+) |
| Rename, delete, move, sync | `edit_others_posts` (Editor+) |
| Protected folder list (read) | `edit_others_posts` (Editor+) |
| Settings | `manage_options` (Administrator) |
| Thumbnails | `manage_options` (Administrator) |
| Security (BDA + IP blocking config) | `manage_options` (Administrator) |
| Toggle BDA on a folder | `manage_options` (Administrator) |
| Add / remove blocked IPs | `manage_options` (Administrator) |

---

## File Structure

```
media-manager/
├── media-manager.php          # Plugin bootstrap, constants
├── uninstall.php              # Full cleanup on plugin delete
├── assets/
│   ├── css/
│   │   ├── admin.css
│   │   └── admin.min.css
│   ├── js/
│   │   ├── mm-library.js          # Bootstrap — creates window.mmLib namespace + DOM boot
│   │   ├── mm-library-tree.js     # jsTree init, context menu, folder CRUD, hover preview, recent filter
│   │   ├── mm-library-grid.js     # loadFolder, fetch/render, pagination, sort controls
│   │   ├── mm-library-files.js    # Checkboxes, bulk actions, rename, drag-drop, sync, upload listener
│   │   ├── mm-upload.js           # Upload handler
│   │   ├── mm-thumbnails.js       # Thumbnail regeneration UI
│   │   └── mm-bda.js              # Security page (protected folders, IP list)
│   └── vendor/
│       └── jstree/                # jsTree 3.3.16 (self-hosted)
└── includes/
    ├── Admin/
    │   ├── class-admin.php        # Help tabs, hook capture
    │   ├── class-assets.php       # Script/style enqueuing
    │   ├── class-menu.php         # Admin menu registration
    │   └── Pages/
    │       ├── class-library-page.php
    │       ├── class-settings-page.php
    │       ├── class-thumbnails-page.php
    │       └── class-bda-page.php
    ├── Ajax/
    │   ├── class-ajax-handler.php  # Thin orchestrator — wires 26 wp_ajax_* actions
    │   └── Handlers/
    │       ├── trait-ajax-helpers.php      # Shared verify() + build_file_data()
    │       ├── class-library-handler.php   # folder_tree, load_folder, folder_contents, sort_contents, folder_thumbs, recent_files, get_orphans
    │       ├── class-file-handler.php      # upload_file, move_copy_file, rename_file, delete_files
    │       ├── class-folder-handler.php    # create_folder, delete_folder, hide_folder, refresh_folders, sync_folder, sync_chunk
    │       ├── class-settings-handler.php  # save_settings
    │       ├── class-thumbnail-handler.php # regen_thumbnails, regen_process
    │       └── class-security-handler.php  # toggle_file_access, get_protected_files, add_blocked_ip, remove_blocked_ips, get_blocked_ips, save_bda_settings
    ├── Core/
    │   ├── class-activator.php    # Table creation, option seeding, initial scan
    │   ├── class-deactivator.php
    │   ├── class-loader.php       # add_action/add_filter registry
    │   ├── class-plugin.php       # Bootstrap, module wiring
    │   └── class-scheduler.php    # Cron job registration
    ├── CPT/
    │   └── class-folder-post.php  # mm_folder post type registration
    ├── Data/
    │   ├── class-file-repository.php
    │   ├── class-folder-repository.php
    │   ├── class-ip-repository.php
    │   └── class-protected-repository.php
    ├── FileSystem/
    │   ├── class-file-manager.php    # Upload, rename, delete, EXIF strip
    │   ├── class-folder-manager.php  # Create, rename, delete folders
    │   ├── class-link-updater.php    # Update post content after rename
    │   └── class-sync-manager.php    # Sync disk → DB
    ├── Helpers/
    │   └── class-path-helper.php
    ├── Security/
    │   ├── class-bda-manager.php  # .htaccess write/remove
    │   └── class-ip-blocker.php   # init-hook IP check
    └── Thumbnails/
        └── class-regen-manager.php
```

---

## Changelog

### 1.1.0
**New features**
- Folder thumbnail hover preview — hover any folder for 350 ms to see up to 4 thumbnails from that folder
- Recent uploads filter — "Last 7 days" / "Last 30 days" quick-filter buttons in the toolbar
- Unassigned files node — virtual tree entry surfaces all attachments with no folder mapping
- Drag-and-drop move — drag file cards onto any tree folder; renames automatically on filename collision
- EXIF strip on upload — new settings checkbox strips GPS and camera metadata from JPEGs via Imagick/GD

**Improvements**
- Pagination replaced with numbered page navigation (prev / page numbers with ellipsis / next); sort and filter modes each maintain independent page state
- Sort controls are now mode-aware — work correctly in Recent and Unassigned views as well as standard folder views
- Default items-per-page lowered from 500 → 50 for faster initial loads
- `AjaxHandler` split into six focused handler classes under `includes/Ajax/Handlers/`; shared nonce/capability gating via `AjaxHelpers` trait
- Library JS split into four files (`mm-library.js` bootstrap + tree, grid, files sub-modules) for easier maintenance
- Capability gates tightened: `save_settings` requires `manage_options`; upload requires `upload_files`; protected-folder list read requires only `edit_others_posts`

**Bug fixes**
- Folder hover preview was anchoring to the wrong ancestor (`position: relative` now set on `#mm-tree-pane`)
- "Unassigned" virtual node no longer appears in the bulk-move folder select
- Settings page checkboxes (disable scaling, skip WebP, strip EXIF) can now be turned off after being saved
- `recent_files` AJAX action previously loaded all matching IDs into PHP then sliced — now uses native `WP_Query` pagination
- Sort controls in Orphan and Recent filter modes now dispatch to the correct fetch function instead of always calling `mm_sort_contents`
- Removed a dead `mouseleave` handler on `#mm-folder-preview` that was never triggered

### 1.0.0
- Initial release
- Physical folder tree with jsTree
- Paginated, sortable file grid with Load More
- Direct file upload into folders
- File rename (updates post title and all content references)
- Bulk delete
- FTP sync (disk → media library)
- Thumbnail regeneration per folder or site-wide
- Block Direct Access via Apache `.htaccess`
- IP address blocking with object-cache backed block list
- Native WordPress media modal for image editing
- Security admin page (BDA + IP blocking settings)
- Full uninstall cleanup
