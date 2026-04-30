# EventPrime — External Dependencies Audit

**Source plugin:** `eventprime-event-calendar-management` v4.0.9.7  
**Audit date:** 2026-03-21  
**Status:** 🟢 Complete

---

## 1. Bundled PHP Libraries

### 1a. TCPDF (`includes/lib/tcpdf_min/`)

| Property | Detail |
|---|---|
| Library | TCPDF (Minimal build) |
| Version | See `VERSION` file in lib directory |
| Purpose | PDF generation for printing attendee lists / tickets |
| Size | ~3 MB bundled |
| Used in | `class-ep-ajax.php` → `event_print_all_attendees()` |
| License | LGPL-3.0 |

**Recommendation:** Replace with a much lighter library (e.g., `dompdf` via Composer or WP's native HTML-to-PDF helpers). Alternatively, generate HTML-only print views and let the browser handle printing. TCPDF at 3 MB is significant for a WordPress plugin and is the heaviest single dependency.

---

### 1b. QR Code Generator (`includes/lib/qrcode.php`)

| Property | Detail |
|---|---|
| Library | `phpqrcode` (custom/bundled fork) |
| Purpose | Generates QR codes for events and bookings |
| Used in | `class-eventprime-functions.php` (3 call sites) |
| License | LGPL-3.0 |
| Size | ~100 KB |

**Recommendation:** Keep as-is for now — it's lightweight. Consider lazy-loading (only `require_once` when actually generating a QR). The current code does a bare `require_once 'lib/qrcode.php'` (relative path) which is fragile — switch to an absolute path via `EP_PLUGIN_FILE`.

---

## 2. Bundled JavaScript Libraries

These are loaded as enqueued WordPress assets from `public/js/` and `admin/js/`.

| Library | Handle(s) | Where Loaded | Purpose |
|---|---|---|---|
| FullCalendar.js | (bundled in main public JS) | Frontend | Calendar month/week/day/agenda views |
| Select2 | `ep-select2-js` / `ep-user-select2-js` | Admin + Frontend | Searchable dropdown fields |
| Flatpickr | `ep-flatpickr-js` | Admin | Date/time pickers |
| Chart.js | `ep-chart-js` | Admin (reports) | Reports charts |
| jQuery Toast | `ep-toast-js` / `ep-admin-toast-js` | Admin + Frontend | Toast notifications |
| Material Icons (icon font) | `ep-material-fonts` | Admin + Frontend | Icon set (loaded as CSS + woff files) |

**Recommendation:** All JS libraries should be loaded conditionally (only on EventPrime pages/screens). Current code has some conditional loading but it's inconsistent. In the rebuild, wrap all `wp_enqueue_*` calls in page-detection guards using `is_eventprime_plugin_page()` (which already exists in `Eventprime_Basic_Functions`).

---

## 3. External API Integrations

### 3a. Google Maps

| Property | Detail |
|---|---|
| Integration | Google Maps JavaScript API |
| Config key | `gmap_api_key` in `em_global_settings` |
| Used for | Venue location display on event detail page |
| Loaded | Frontend, conditionally when `gmap_api_key` is set and venue has coordinates |
| Data stored | Latitude (`em_venue_lat`), longitude (`em_venue_lng`) in term meta |

**Rebuild note:** Keep. Load the Maps script only when a venue with coordinates is present on the page.

---

### 3b. OpenWeatherMap

| Property | Detail |
|---|---|
| Integration | OpenWeatherMap Current Weather API |
| Config key | `weather_api_key` in `em_global_settings` |
| Used for | Weather widget on event detail page |
| Loaded | Frontend, conditionally when `weather_api_key` is set and `hide_weather_tab = false` |
| Unit | Metric (Celsius) by default; Fahrenheit toggle available |

**Rebuild note:** Keep as optional feature. Gate behind API key presence. The API call appears to be made client-side (JS fetch) — verify in JS assets.

---

### 3c. Google Calendar

| Property | Detail |
|---|---|
| Integration | Google Calendar API (OAuth) |
| Config keys | `google_cal_client_id`, `google_cal_api_key` |
| Used for | "Add to Google Calendar" link on event detail |
| Loaded | Frontend, when `gcal_sharing = 1` |

**Rebuild note:** Keep. The current `gcal_link()` generates a pre-filled Google Calendar URL — this doesn't require the API keys and works without OAuth. The OAuth keys appear to be for a more advanced sync feature (possibly an extension). Clarify scope.

---

### 3d. Google reCAPTCHA v2

| Property | Detail |
|---|---|
| Integration | Google reCAPTCHA v2 ("I'm not a robot") |
| Config keys | `google_recaptcha_site_key`, `google_recaptcha_secret_key` |
| Used on | Login form, Register form, Checkout register form |
| Toggle | Per-form setting (`login_google_recaptcha`, `register_google_recaptcha`, `checkout_reg_google_recaptcha`) |

**Rebuild note:** Keep. Load the reCAPTCHA script only when it's enabled on the current form.

---

### 3e. PayPal (Standard / Smart Buttons)

| Property | Detail |
|---|---|
| Integration | PayPal JS SDK (Smart Payment Buttons) |
| Config keys | `paypal_client_id`, `paypal_processor` (sandbox/live) |
| Used for | Event ticket checkout payments |
| Flow | Client-side Smart Buttons → AJAX `paypal_sbpr` → server-side order verification |
| Class | `EventM_Paypal_Service` (`includes/class-ep-paypal-service.php`) |

**Rebuild note:** Keep. Wrap the PayPal SDK script load inside the checkout flow only.

---

## 4. WordPress Plugin Dependencies

The plugin uses `is_plugin_active()` / class existence checks for optional integrations:

| Plugin / Class | Check Method | Used For |
|---|---|---|
| RegistrationMagic | `ep_is_registration_magic_active()` → checks for RM class | Alternative registration form |
| Live Seating (EP extension) | `in_array('live_seating', $extensions)` | Seating chart on tickets |
| Stripe (EP extension) | Referenced in settings | Payment gateway |
| Coupon Codes (EP extension) | Referenced in extension loader | Discount codes at checkout |

No hard dependencies — all optional integrations degrade gracefully when the companion plugin is absent.

---

## 5. WordPress Core Dependencies

| Feature Used | Notes |
|---|---|
| `WP_Query` | Core event and performer queries |
| `WP_User_Query` | User lookups for checkout registration |
| `wp_mail()` | All notification emails |
| `WP_HTTP` / `wp_remote_get` | External API calls (weather, license verification) |
| `wp_cache_set / get` | Object caching for post queries (1-hour TTL, group `eventprime_posts`) |
| `WP_List_Table` | Reports booking list table |
| `dbDelta()` | Custom table creation/upgrade |
| `wp_nonce_field / check_admin_referer` | Form security |
| `current_user_can()` | Capability checks throughout |

---

## 6. Dependency Map for Rebuild

```
EventPrime (rebuild)
├── PHP
│   ├── phpqrcode (keep, fix require path)      ~100 KB
│   └── PDF generation                          Replace TCPDF → lighter alternative
│
├── JavaScript (bundled)
│   ├── FullCalendar.js                         Keep, load conditionally
│   ├── Select2                                 Keep, load conditionally
│   ├── Flatpickr                               Keep, admin-only
│   ├── Chart.js                                Keep, reports page only
│   └── jQuery Toast                            Keep (or replace with native WP notice)
│
├── External APIs (optional, key-gated)
│   ├── Google Maps JS API                      Keep
│   ├── OpenWeatherMap                          Keep
│   ├── Google Calendar OAuth                   Keep (basic link only)
│   └── Google reCAPTCHA v2                     Keep
│
└── Payment Gateways
    ├── PayPal Smart Buttons (free)             Keep
    └── Stripe, Offline (paid extensions)       Out of scope for free build
```

---

## 7. Things to Remove

| Item | Reason |
|---|---|
| TCPDF full library (`tcpdf_min/`) | 3 MB for PDF that could be an HTML print view |
| Material Icons woff files × 6 | Use CDN or subset font; 6 woff files is excessive |
| Legacy font directories (`ae_fonts_2.0/`, `dejavu*`, `freefont*`) | Only needed if TCPDF is retained for complex PDF output |
| `registration_Old.php` email template | Orphaned file |
| Deactivation feedback AJAX handler | Upsell mechanism |
| `ep_list_all_exts()` / extensions marketplace page | Upsell mechanism |
