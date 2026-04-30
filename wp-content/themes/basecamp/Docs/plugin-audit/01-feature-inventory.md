# EventPrime — Feature Inventory

**Source plugin:** `eventprime-event-calendar-management` v4.0.9.7  
**Audit date:** 2026-03-21  
**Status:** 🟢 Complete

---

## Legend

| Symbol | Meaning |
|---|---|
| ✅ Keep | Port to rebuild as-is |
| 🔄 Redesign | Keep the capability, rethink the implementation |
| ❌ Drop | Not needed in rebuild |
| ⚠️ Investigate | Needs more context before deciding |

---

## 1. Core Event Management

| Feature | Detail | Decision | Notes |
|---|---|---|---|
| Custom post type `em_event` | Events stored as CPT | ✅ Keep | Solid WP-native approach |
| Event title & description | Standard WP editor support | ✅ Keep | |
| Featured image / gallery | `em_gallery_image_ids` post meta | ✅ Keep | Gallery as meta array is fine |
| Event start / end date+time | `em_start_date`, `em_end_date`, `em_start_time`, `em_end_time` + composite `em_start_date_time` as timestamp | 🔄 Redesign | Store as single UTC timestamp pair; remove redundant fields |
| Multi-day events | Covered by start/end range | ✅ Keep | |
| All-day events | Flag in post meta | ✅ Keep | |
| Recurring events | `em_recurrence_interval`, complex child-event creation logic | 🔄 Redesign | Move to dedicated `Recurrence` model class; recurrence rule stored as JSON |
| Child events (recurrence) | Parent/child relationship via post meta | 🔄 Redesign | Use `post_parent` properly, not ad-hoc meta |
| Event status (custom) | `emexpired`, `expired`, `cancelled`, `pending`, `refunded`, `completed` | 🔄 Redesign | Keep statuses; clean up duplication between `emexpired` and `expired` |
| Event display on frontend | Single-event template via `the_content` filter | 🔄 Redesign | Use proper `template_include` filter, not content injection |
| iCal download | `get_ical_file()` hooked to `init` | ✅ Keep | |
| Google Calendar link | `gcal_link()` in notification service | ✅ Keep | |
| Social sharing | Icons on event detail; flag controlled by setting | ✅ Keep | |
| QR code on event | Inline QR generation via `QRcode` lib | 🔄 Redesign | Generate on demand, cache result |
| Event wishlist | User meta-based wishlist | ✅ Keep | |
| Event duplication | Bulk action in admin list | ✅ Keep | |
| Featured events | `em_is_featured` post meta flag | ✅ Keep | |

---

## 2. Ticketing & Pricing

| Feature | Detail | Decision | Notes |
|---|---|---|---|
| Ticket types (individual) | Stored in custom table `{prefix}em_price_options` | ✅ Keep | Table schema is reasonable |
| Ticket categories | Stored in `{prefix}eventprime_ticket_categories` | ✅ Keep | |
| Free events | Price = 0, bookings still tracked | ✅ Keep | |
| Paid events | Price set per ticket | ✅ Keep | |
| Special/sale price | `special_price` field on ticket | ✅ Keep | |
| Ticket capacity | Per-ticket and per-category capacity | ✅ Keep | |
| Capacity progress bar | Toggle per ticket | ✅ Keep | |
| Show remaining tickets | Toggle per ticket | ✅ Keep | |
| Ticket booking date windows | `booking_starts` / `booking_ends` per ticket (custom date, event date, or relative) | 🔄 Redesign | Clean up the three-mode date logic into a value object |
| Min/max tickets per booking | `min_ticket_no`, `max_ticket_no` per ticket | ✅ Keep | |
| Ticket visibility by role | `visibility` JSON per ticket | ✅ Keep | |
| Offers/discounts | `offers` JSON per ticket; complex conditional evaluation | 🔄 Redesign | Extract to `OfferEvaluator` service |
| Additional fees | `additional_fees` JSON; applied at checkout total | ✅ Keep | |
| Multi-currency | Single `currency` setting; position (before/after) configurable | ✅ Keep | |
| Ticket template | `ticket_template_id` per ticket | ⚠️ Investigate | Appears to be a paid-extension feature |
| Seat data | `seat_data` JSON per ticket | ⚠️ Investigate | Live Seating is a paid extension |

---

## 3. Bookings & Checkout

| Feature | Detail | Decision | Notes |
|---|---|---|---|
| Custom post type `em_booking` | Bookings as CPT (non-public) | ✅ Keep | |
| Booking statuses | `completed`, `cancelled`, `refunded`, `pending`, `failed` | ✅ Keep | |
| Custom checkout fields | Stored in `{prefix}eventprime_checkout_fields` table | ✅ Keep | |
| Per-attendee fields | `em_event_checkout_attendee_fields` post meta (JSON) | ✅ Keep | |
| Checkout timer | Session-based timer; configurable minutes | ✅ Keep | |
| Guest checkout (anonymous) | Booking without WP account | ✅ Keep | |
| Booking edit | `load_edit_booking_attendee_data` AJAX | ✅ Keep | |
| Booking cancellation | User-initiated and admin-initiated | ✅ Keep | |
| Booking notes | Admin-only notes on booking | ✅ Keep | |
| Bulk booking export (CSV) | Admin bulk action on booking list | ✅ Keep | |
| Print all attendees | PDF generation via TCPDF | 🔄 Redesign | Use a lightweight PDF lib or WP's built-in; TCPDF is 3MB+ bundled |
| Booking QR code | QR per booking | ✅ Keep | |
| Cart / ticket quantity update | AJAX `update_tickets_data` | ✅ Keep | |

---

## 4. Payment Gateways

| Feature | Detail | Decision | Notes |
|---|---|---|---|
| PayPal (Standard) | `EventM_Paypal_Service`; IPN / SBPR flow | ✅ Keep | |
| Stripe | Referenced in settings but implemented as paid extension | ⚠️ Investigate | No Stripe code in free plugin |
| Offline payment | Referenced in extensions list | ❌ Drop | Out of scope for rebuild |
| Default payment processor setting | `default_payment_processor` option | ✅ Keep | |
| Currency settings | Currency code + position (before/after symbol) | ✅ Keep | |

---

## 5. Performer (Custom Post Type)

| Feature | Detail | Decision | Notes |
|---|---|---|---|
| CPT `em_performer` | Performers/speakers as posts | ✅ Keep | |
| Performer meta | Name, bio, images, social links, phones, websites, emails | ✅ Keep | |
| Performer detail page | Single template | ✅ Keep | |
| Performer → Event relationship | `em_performer` post meta on event | ✅ Keep | |
| Upcoming events per performer | `get_upcoming_events_for_performer()` | ✅ Keep | |
| Featured performers widget | `get_featured_event_performers()` | ✅ Keep | |
| Popular performers widget | `get_popular_event_performers()` | ✅ Keep | |

---

## 6. Venues (Taxonomy)

| Feature | Detail | Decision | Notes |
|---|---|---|---|
| Taxonomy `em_venue` | Venues as terms on `em_event` | 🔄 Redesign | Consider CPT instead of taxonomy; venues need rich data (address, map, capacity) |
| Venue detail page | Archive-style template | ✅ Keep | |
| Google Maps embed | `gmap_api_key` setting; lat/lng in term meta | ✅ Keep | |
| Venue capacity | Term meta | ✅ Keep | |
| Upcoming events per venue | Query + load-more AJAX | ✅ Keep | |
| Featured / popular venue widgets | Widget classes | ✅ Keep | |

---

## 7. Event Types (Taxonomy)

| Feature | Detail | Decision | Notes |
|---|---|---|---|
| Taxonomy `em_event_type` | Hierarchical; single-value per event | ✅ Keep | |
| Type color | Displayed on calendar and cards | ✅ Keep | |
| Type image | Term meta | ✅ Keep | |
| Type detail page | Template | ✅ Keep | |
| Featured / popular type widgets | Widget classes | ✅ Keep | |

---

## 8. Organizers (Taxonomy)

| Feature | Detail | Decision | Notes |
|---|---|---|---|
| Taxonomy `em_event_organizer` | Organizers as terms on `em_event` | 🔄 Redesign | Same reasoning as Venues — rich data fits CPT better |
| Organizer detail page | Template | ✅ Keep | |
| Social links | Term meta | ✅ Keep | |
| Featured / popular organizer widgets | Widget classes | ✅ Keep | |

---

## 9. Calendar Views (Frontend)

| Feature | Detail | Decision | Notes |
|---|---|---|---|
| Month view | FullCalendar.js based | ✅ Keep | |
| Week view | FullCalendar.js | ✅ Keep | |
| Day view | FullCalendar.js | ✅ Keep | |
| Agenda / list-week view | FullCalendar.js | ✅ Keep | |
| Square grid view | Custom card grid | ✅ Keep | |
| Staggered / masonry grid | Custom | ✅ Keep | |
| Slider / carousel view | Custom | ✅ Keep | |
| Rows view | Custom stacked rows | ✅ Keep | |
| Drag-and-drop event date change (admin calendar) | AJAX `calendar_events_drag_event_date` | ✅ Keep | |
| Calendar admin view (WP dashboard) | Custom calendar screen | ✅ Keep | |
| Event color inherited from type | On calendar and card | ✅ Keep | |
| Switch view toggle (frontend) | Configurable which views to show | ✅ Keep | |
| Default calendar view setting | `default_cal_view` | ✅ Keep | |

---

## 10. Frontend Submission (FES)

| Feature | Detail | Decision | Notes |
|---|---|---|---|
| Event submission form | `[em_event_submit_form]` shortcode | ✅ Keep | |
| Role-based submission access | Configurable roles | ✅ Keep | |
| Anonymous submission | Optional setting | ✅ Keep | |
| Event moderation (pending → publish) | `ues_default_status` option | ✅ Keep | |
| User can delete own event | `fes_allow_user_to_delete_event` setting | ✅ Keep | |
| Configurable FES sections | Which fields appear on the form | ✅ Keep | |

---

## 11. User Area / Profile

| Feature | Detail | Decision | Notes |
|---|---|---|---|
| User profile page | `[em_profile]` shortcode | ✅ Keep | |
| My bookings tab | List of user's bookings | ✅ Keep | |
| Upcoming bookings tab | Future bookings only | ✅ Keep | |
| My events tab | FES-submitted events | ✅ Keep | |
| My wishlist tab | Wishlisted events | ✅ Keep | |
| Transaction history | Booking payment log | ✅ Keep | |
| Login shortcode | `[em_login]` | ✅ Keep | |
| Register shortcode | `[em_register]` | ✅ Keep | |
| Custom login/register forms | EP's own forms (not WP default) | ✅ Keep | |
| reCAPTCHA on login/register | Google reCAPTCHA v2 | ✅ Keep | |
| Logout redirect | Hook on `logout_redirect` | ✅ Keep | |
| Timezone detection (user) | JS → AJAX `update_user_timezone` | ✅ Keep | |

---

## 12. Notifications / Email

| Feature | Detail | Decision | Notes |
|---|---|---|---|
| Booking confirmed (customer) | `booking_confirmed()` | ✅ Keep | |
| Booking pending | `booking_pending()` | ✅ Keep | |
| Booking cancelled | `booking_cancel()` | ✅ Keep | |
| Booking refunded | `booking_refund()` | ✅ Keep | |
| Admin booking confirmed | Admin copy of booking email | ✅ Keep | |
| User registration welcome | `user_registration()` | ✅ Keep | |
| Password reset | `reset_password_mail()` | ✅ Keep | |
| Event submitted (FES) | `event_submitted()` | ✅ Keep | |
| Event approved | `event_approved()` | ✅ Keep | |
| CC fields on emails | Per-email CC setting | ✅ Keep | |
| Bulk email to attendees | Admin screen; event-picker + compose | 🔄 Redesign | Use WP Cron for bulk send to avoid timeouts |
| Configurable email templates | HTML templates in `admin/partials/settings/emailers/mail/` | 🔄 Redesign | Move to DB-stored templates with a simple token system |
| Disable admin/frontend emails | Global toggles | ✅ Keep | |

---

## 13. Shortcodes

| Shortcode | Description | Decision |
|---|---|---|
| `[em_events]` | Events list / calendar | ✅ Keep |
| `[em_event]` | Single event embed | ✅ Keep |
| `[em_event_types]` | Event types listing | ✅ Keep |
| `[em_event_type]` | Single event type | ✅ Keep |
| `[em_sites]` | Venues listing | ✅ Keep |
| `[em_event_site]` | Single venue | ✅ Keep |
| `[em_performers]` | Performers listing | ✅ Keep |
| `[em_performer]` | Single performer | ✅ Keep |
| `[em_event_organizers]` | Organizers listing | ✅ Keep |
| `[em_event_organizer]` | Single organizer | ✅ Keep |
| `[em_booking]` | Checkout / booking form | ✅ Keep |
| `[em_booking_details]` | Booking detail page | ✅ Keep |
| `[em_profile]` | User profile area | ✅ Keep |
| `[em_login]` | Login form | ✅ Keep |
| `[em_register]` | Register form | ✅ Keep |
| `[em_event_submit_form]` | Frontend event submission | ✅ Keep |

---

## 14. Gutenberg Blocks

| Block | Description | Decision |
|---|---|---|
| `ep-square-cards-block` | Events as square cards | ✅ Keep |
| `ep-booking-details-block` | Booking detail | ✅ Keep |
| `ep-booking-process-block` | Checkout flow | ✅ Keep |
| `ep-login-block` | Login form | ✅ Keep |
| `ep-register-block` | Register form | ✅ Keep |
| `ep-organizer-block` | Organizer listing | ✅ Keep |
| `ep-user-profile-block` | Profile area | ✅ Keep |
| `ep-venues-block` | Venues listing | ✅ Keep |

> All blocks are wrappers around the same shortcode render functions — that pattern is fine.

---

## 15. Widgets (Legacy)

| Widget | Class |Decision |
|---|---|---|
| Event calendar | `class-event-calendar.php` | 🔄 Redesign | Block replacement preferred |
| Event countdown | `class-event-countdown.php` | 🔄 Redesign | |
| Event slider | `class-event-slider.php` | 🔄 Redesign | |
| Featured event types | `class-featured-event-types.php` | ❌ Drop | Block covers this |
| Featured performers | `class-featured-event-performers.php` | ❌ Drop | |
| Featured venues | `class-featured-event-venues.php` | ❌ Drop | |
| Featured organizers | `class-featured-event-organizers.php` | ❌ Drop | |
| Popular event types | `class-popular-event-types.php` | ❌ Drop | |
| Popular performers | `class-popular-event-performers.php` | ❌ Drop | |
| Popular venues | `class-popular-event-venues.php` | ❌ Drop | |
| Popular organizers | `class-popular-event-organizers.php` | ❌ Drop | |

---

## 16. Admin Reports

| Feature | Detail | Decision |
|---|---|---|
| Bookings report | Stat overview + list; filterable | ✅ Keep |
| Payments report | Revenue summary | ✅ Keep |
| Attendees report / export | Attendee list per event; CSV export | ✅ Keep |

---

## 17. Extensions System

| Feature | Detail | Decision |
|---|---|---|
| Extension loader | `ep_get_activate_extensions()` checks for installed extensions | ✅ Keep |
| Extensions page (admin) | Marketplace listing inside plugin | ❌ Drop | Link to external page instead |
| Live Seating | Paid extension; `seat_data` on tickets | ⚠️ Investigate | Keep data structure; implement if needed |
| Stripe | Paid extension | ⚠️ Investigate | |
| Coupon codes | Paid extension | ⚠️ Investigate | |

---

## 18. Dead Weight (Confirmed Drop)

| Feature | Reason |
|---|---|
| `registration_Old.php` email template | Orphaned legacy file |
| Inline `<script>` in `define_admin_hooks()` for notice repositioning | Should be a proper JS asset |
| Commented-out hooks (dozens of `// $this->loader->add_action(...)`) | Remove entirely |
| `ep_old_ext_data()` | Legacy migration shim |
| `eventprime_check_event_booking_by_user_old()` | Superseded by v2 method |
| Feedback/deactivation modal | Upsell mechanism — out of scope |
| Premium banner in admin footer | Upsell mechanism — out of scope |
| `plugin-feedback.php` partial | Upsell mechanism — out of scope |
| `flush_rewrite_rules()` inside `register_post_types()` | Should only run on activation, not every `init` |
| Custom admin footer banner | `add_eventprime_admin_footer_banner` — out of scope |
