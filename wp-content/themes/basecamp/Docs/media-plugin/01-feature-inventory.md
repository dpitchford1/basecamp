# Media Manager — Feature Inventory

**Status:** ✅ Complete  
**Last updated:** 2026-05-04  
**Source audited:** `media-library-plus/media-library-plus.php` (v8.3.3, ~7,547 lines) + 12 include files

---

## Legend

| Symbol | Meaning |
|---|---|
| ✅ Keep | Carry forward into rebuild as-is or equivalent |
| 🔄 Redesign | Keep the concept, rebuild the implementation |
| ❌ Drop | Remove entirely from new plugin |
| ⚠️ Deferred | Out of scope for v1; may revisit |

---

## 1. Core — Folder Management

| Feature | Status | Notes |
|---|---|---|
| Create new folder (physical dir on disk) | ✅ Keep | Core feature. AJAX-driven in old plugin; keep pattern. |
| Delete folder (with empty check) | ✅ Keep | Only allows deletion of empty folders — good guard; keep. |
| Hide a folder from library (without deleting from disk) | 🔄 Redesign | Old plugin writes `mlpp-hidden` sentinel file to disk. Redesign as a DB flag or meta instead. |
| Folder tree navigation (left-panel tree) | ✅ Keep | jsTree library for the tree UI; evaluate keeping vs. replacing in rebuild. |
| Folder tree — right-click context menu | ✅ Keep | Hide/Delete actions. Keep but clean up implementation. |
| Refresh folder tree (poll for new server-side folders) | ✅ Keep | Useful for FTP-added folders. Consolidate into sync. |
| Auto-detect new folders on schedule (daily cron) | 🔄 Redesign | Daily `wp_schedule_event`. Keep the cron, re-evaluate frequency. |
| Sub-folder support (hierarchical) | ✅ Keep | CPT is registered as `hierarchical = true`; folder-in-folder works. |
| Folder rename | ❌ Drop | Old plugin explicitly disallows it (breaks embedded links). Excluded by design. |

---

## 2. Core — File Operations

| Feature | Status | Notes |
|---|---|---|
| Upload files to a specific folder | ✅ Keep | Custom upload AJAX handler targeting a folder path. |
| Drag-and-drop multi-file upload | ✅ Keep | Core UX; keep the pattern, rebuild the JS cleanly. |
| Move file to another folder (drag-to-folder) | ✅ Keep | Physical file move + update all embedded post/page links. |
| Copy file to another folder | ✅ Keep | Creates a duplicate attachment in WP + copies physical file. |
| Move/copy toggle (UI switch) | ✅ Keep | Simple UX toggle; keep. |
| Delete one or more files | ✅ Keep | Deletes WP attachment + physical file. |
| Rename a file | ✅ Keep | Renames physical file + updates attachment record + embedded links. |
| Select all / select range (shift-click) | ✅ Keep | Standard multi-select UX. |
| Bulk actions dropdown | ✅ Keep | Select action → Apply. Clean pattern to keep. |
| Hide a file (remove from library without deleting) | 🔄 Redesign | Review if still needed; if so, implement as a DB status flag. |
| Edit file metadata (title, alt, caption) | ✅ Keep | Opens WP's native edit-attachment screen in new tab. No custom logic needed. |

---

## 3. Core — Sync

| Feature | Status | Notes |
|---|---|---|
| Sync current folder (scan disk, import new files) | ✅ Keep | Picks up FTP-uploaded files and adds them to WP Media Library. Critical feature. |
| Scan all attachments on first activation | ✅ Keep | One-time activation scan to map existing uploads to folder structure. |
| Skip `.webp` files during sync | ✅ Keep | Prevents auto-generated WebP variants from cluttering library. |
| Background sync process (chunked AJAX) | 🔄 Redesign | Old sync is chunked AJAX; fine approach but clean up the implementation. |

---

## 4. Core — Display & Sorting

| Feature | Status | Notes |
|---|---|---|
| Items per page setting | ✅ Keep | Default 500; configurable. |
| Sort by date (upload date) | ✅ Keep | |
| Sort by title (filename) | ✅ Keep | |
| Sort order toggle (ASC / DESC) | ✅ Keep | |
| Persist sort preference per-user | 🔄 Redesign | Old plugin stores in `wp_options` globally; rebuild should use user meta. |
| File thumbnail grid display | ✅ Keep | Standard media grid. |
| File type icons for non-image files | ✅ Keep | |

---

## 5. Thumbnail Regeneration

| Feature | Status | Notes |
|---|---|---|
| Regenerate thumbnails for selected files | ✅ Keep | Bulk-select images, trigger regen. Useful as a built-in tool. |
| Skip SVG files during regeneration | ✅ Keep | SVGs have no raster thumbnails; auto-skip is correct. |
| Per-image AJAX processing | ✅ Keep | Chunked/queued approach is correct for large sets. |

---

## 6. Block Direct Access (BDA) — Partial Keep

The BDA system in the old plugin is large. Only the following sub-features are kept:

| Feature | Status | Notes |
|---|---|---|
| Protected content directory (`protected-content/`) | ✅ Keep | Physical directory outside webroot-accessible paths; `.htaccess` protected. |
| Move files into / out of protected directory | ✅ Keep | Toggle block status on individual files. |
| IP address block list (add/remove IPs) | ✅ Keep | Block specific IPs from accessing download links. |
| View protected files report | ✅ Keep | Admin screen listing all protected files. |
| Custom no-access page (redirect for blocked requests) | ✅ Keep | Let admin pick an existing WP page as the "access denied" destination. |
| Prevent directory listing (`.htaccess`) | ✅ Keep | Write `Options -Indexes` to protected dir `.htaccess`. |
| Prevent hotlinking (`.htaccess`) | ✅ Keep | Write `RewriteRule` to block external referrers. |
| Download links with count limit / expiry date | ❌ Drop | Complex, not used. |
| Auto-protect new uploads | ❌ Drop | Not needed. |
| Display protected images on frontend (base64 proxy) | ❌ Drop | Fragile, performance-hostile approach. Drop entirely. |
| Disable right-click on images | ❌ Drop | Ineffective, JavaScript-only, easily bypassed. |
| Download page template (`page-mlfp-download.php`) | ❌ Drop | Tied to download link feature; dropped with it. |
| BDA file report with download counts | ❌ Drop | Tied to download limit feature. |

---

## 7. Settings & Configuration

| Feature | Status | Notes |
|---|---|---|
| Items per page | ✅ Keep | |
| Disable large image scaling | ✅ Keep | Wraps WP's `big_image_size_threshold` filter. |
| Add postmeta index (performance) | 🔄 Redesign | Good intent; rebuild should handle this cleanly at activation. |
| Skip WebP files on sync | ✅ Keep | |
| Move vs. Copy as default behaviour | ✅ Keep | Global default for drag-and-drop action. |

---

## 8. Drop — Entirely

These features are fully removed and will not appear in the PRD:

| Feature | Reason |
|---|---|
| MaxGalleria gallery integration (`add_to_max_gallery`) | MaxGalleria not installed; external dependency. |
| NextGen Gallery integration (`mlpp_create_new_ng_gallery`) | External dependency; not used. |
| Image SEO bulk tool (alt/title auto-set) | Not needed per decision. |
| Review / feature notice system (upsell nudges) | Third-party promotional scaffolding. |
| Upgrade-to-Pro links and ads | Third-party upsell. |
| Support pages (FAQ, system info, tips, articles) | Vendor support pages; irrelevant for own plugin. |
| Update suppression filter (`site_transient_update_plugins`) | Only needed because we patched a vendor plugin; moot for own build. |
| Plugin version notice system | Replace with standard WP plugin header versioning. |
| Hidden/commented-out dead code | Several `//add_action` calls in the old plugin; do not carry forward. |
