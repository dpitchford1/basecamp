# Managing Media & Folders

The site uses **Media Library Folders** in place of the default WordPress media grid. All images are stored in real filesystem folders — not virtual tags or database-only categories. This means the folder structure you see in the admin mirrors the actual directory layout on the server.

> **For developers:** The plugin is *Media Library Folders* v8.3.2 by Max Foundry (`/wp-content/plugins/media-library-plus`). It hooks into `wp_handle_upload` and `mla_handle_upload` — the theme's WebP conversion module already handles both of these hooks so WebP generation fires correctly on all uploads regardless of upload path. The plugin is gitignored (local dev only, installed on each environment separately). A refactor of the folder structure is planned.

---

## Finding the Media Library

Go to **Media → Media Library** in the left admin menu. You will see a two-panel layout:

- **Left panel** — folder tree. Click any folder to browse its contents.
- **Right panel** — thumbnails of files in the selected folder.

Do not use **Media → Add New** for routine uploads — that bypasses the folder system and dumps files into the root uploads directory.

---

## Uploading Images

1. Navigate to the folder you want to upload into (click it in the left panel).
2. Click **Add Files** (or drag and drop directly onto the right panel).
3. The files are uploaded into that folder — they appear immediately as thumbnails.

**Before uploading, check:**
- Is the image at least **1400px wide** for full-width use?
- Is it a JPEG or PNG? (The site converts to WebP automatically on upload — you don't need to prepare a WebP file yourself.)
- Is the filename descriptive? Avoid generic names like `IMG_4521.jpg`. Use `sector-waterpark-hero.jpg` or similar — it helps with SEO and future searches.

---

## Creating Folders

1. Click the parent folder you want to create a subfolder inside.
2. Click **Add Folder** (appears in the toolbar above the file panel).
3. Type a name and confirm.

Suggested folder structure to keep things organised:

```
uploads/
├── sectors/
│   ├── waterparks/
│   ├── theme-parks/
│   └── ...
├── news/
├── pages/
│   ├── home/
│   └── about/
└── branding/
```

There is no enforced structure — this is a guideline. Consistency makes it easier to find files later.

---

## Moving Files

1. Select one or more files (checkbox on each thumbnail).
2. Click **Move** in the toolbar.
3. Choose the destination folder from the popup tree.
4. Confirm.

> **Important:** Moving a file through the Media Library Folders UI is safe — it updates WordPress's attachment records so existing page/post references still work. Do **not** move files via FTP or file manager; that breaks the database links.

---

## Renaming & Deleting

- **Rename:** Click a file to open its details, then edit the filename field. Save.
- **Delete:** Select the file(s) and click **Delete**. This permanently removes the file from the server and from WordPress — make sure it is not in use on any page before deleting.

---

## Using Images in Content

Once uploaded, images can be inserted into posts and pages in the usual WordPress way:

1. In the post editor, click the **+** block inserter and choose **Image**.
2. Click **Media Library** in the image block.
3. Browse or search for your image and click **Select**.

For metabox fields (Sector galleries, featured images, etc.) follow the instructions in the relevant section of these docs — each has its own insert button that opens the media picker.

---

## Searching for Images

Use the **Search** field at the top of the right panel to find files by name within the currently selected folder. To search across all folders, click the root folder in the left panel first.
