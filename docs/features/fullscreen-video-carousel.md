# Fullscreen Video Carousel

## Overview

A minimal, fast, and accessible fullscreen video carousel for WordPress.

---

## Requirements

- **Fullscreen video carousel**: Each video fills the viewport, users can navigate between slides.
- **Usable & accessible**: Keyboard navigation, ARIA roles, focus management, screen reader support.
- **Minimal code & fast**: Lightweight, no unnecessary dependencies, optimized for performance.
- **Lazy loading videos**: Only load/play videos when visible.
- **Poster images**: Show a static image before video loads/plays.
- **HTML overlay text**: Each slide can display overlay text (e.g., title, description). Overlay text may include CSS animations for enhanced presentation.
- **Play/Pause button**: Manual play/pause control for video, since many browsers block autoplay.
- **Separate audio file support**: Each slide can have an associated audio file with play/pause controls; audio is off by default.
- **Responsive video sources**: Load different video files for mobile and desktop users to optimize performance and quality (e.g., use cropped/compressed files as available).
- **Video transitions**: Videos will transition in place (fade/crossfade), not slide horizontally/vertically.

---

## Implementation Plan

### 1. Component Structure

- Carousel wrapper (fullscreen, handles navigation)
- Carousel slides (video, poster, overlay text)
- Navigation controls (prev/next buttons, keyboard support)
- ARIA roles and attributes

### 2. Technologies

- HTML, CSS (layout, fullscreen, overlays)
- JavaScript (carousel logic, lazy loading, accessibility)
- WordPress integration (shortcode or block)
- **Optional:** Consider using [Swiper](https://swiperjs.com/) for the carousel base. Swiper is lightweight, highly customizable, and accessible. It can save development time while allowing us to keep dependencies minimal.

### 3. Performance

- Use `loading="lazy"` for poster images
- Only load/play the video in the active slide
- Pause/unload videos when not active

### 4. Accessibility

- Keyboard navigation (arrow keys, tab focus)
- ARIA roles (e.g., `region`, `listbox`, `option`)
- Visible focus indicators

---

## Open Questions

- How will videos/posters/text be defined? (e.g., PHP array, ACF, shortcode attributes)
- Preferred integration: **template part** (straight code in the page template).
- Any design/wireframe references?  
  **Answer:** No design/wireframe references at this time. We'll focus on a clean, usable UI with play/pause, audio play/pause, next/previous buttons, and text overlay.
- Should we support YouTube/Vimeo or just self-hosted videos?  
  **Answer:** Only self-hosted videos are required; no external services.
- **Transition style:** Videos will transition in place (e.g., fade/crossfade). Confirm preferred animation style.
- **Admin UI:** Confirm if we want a custom meta box for all fields (video, poster, overlay text, audio) or use built-in custom fields.
- **Data structure:** Define how multiple slides are stored (e.g., repeater field, serialized array, multiple meta fields).
- **Accessibility:** Any specific accessibility requirements beyond standard ARIA/keyboard support?
- **Testing:** List of browsers/devices for QA.
- **Design:** Any color/font/branding constraints?
- **Note:** We will **not** be using blocks. Need to decide between built-in custom fields or a custom meta box for managing carousel data (including poster images). `page-video.php` template and corresponding admin page already exist.

---

## WordPress Upload Limitations

- **File formats**: By default, WordPress allows uploading of common video formats such as MP4, MOV, WMV, AVI, MPG, OGV, 3GP, and 3G2. Some formats may be restricted for security reasons. Additional formats can be enabled via filters.
- **File size**: The maximum upload size is determined by server settings (`upload_max_filesize`, `post_max_size` in php.ini). Typical defaults are 2MB–64MB, but this can be increased by adjusting server configuration or using plugins.
- **Note**: We'll use **MP4** to start. If needed, we can enable `.webm` support via a filter in the theme.
- **How to increase upload size:**  
  1. **php.ini:**  
     Increase these values (if you have access):  
     ```
     upload_max_filesize = 128M
     post_max_size = 128M
     ```
  2. **.htaccess:**  
     Add:  
     ```
     php_value upload_max_filesize 128M
     php_value post_max_size 128M
     ```
  3. **wp-config.php:**  
     Add:  
     ```php
     @ini_set( 'upload_max_size' , '128M' );
     @ini_set( 'post_max_size', '128M');
     ```
  4. **Ask your host:**  
     If you can't change these files, contact your hosting provider for support.

- After making changes, you may need to restart your web server or PHP process.

- **Troubleshooting if changes don't work:**
  - Check your actual PHP limits by uploading a file and/or using a plugin like "Site Health" or "PHP Info".
  - Some hosts override `wp-config.php` and `.htaccess` settings—changes may need to be made in your hosting control panel (e.g., cPanel, Plesk).
  - If using local dev tools (MAMP, XAMPP, etc.), update the correct `php.ini` file and restart Apache/nginx/PHP.
  - Some managed hosts require you to contact support to increase limits.
  - Check for typos or misplaced code in `wp-config.php`.
  - Try increasing `memory_limit` as well:
    ```php
    @ini_set( 'memory_limit', '256M' );
    ```

---

## Suggestions & Additional Considerations

- **Security:** Ensure file uploads are validated and sanitized. Only allow trusted users to manage carousel content.
- **Fallbacks:** Provide fallback images or messages if a video fails to load.
- **Performance:** Consider preloading the next/previous video poster for smoother transitions.
- **Custom Meta Box:** A repeater-style meta box (one row per slide: video, poster, overlay text, audio) will make admin management easier and more scalable.
- **Internationalization:** If needed, make overlay text translatable.
- **Analytics:** Optionally track video/audio play events for engagement insights.
- **Extensibility:** Structure code so new fields (e.g., CTA buttons, links) can be added later with minimal refactoring.
- **Documentation:** Document admin usage and front-end integration for future maintainers.

---

## Next Steps

1. Confirm requirements and answer open questions.
2. Decide on data source and integration method.
3. Outline file structure and begin implementation.

---
