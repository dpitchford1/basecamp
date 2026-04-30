# Dish Events — Ticketing Architecture Spec

**Document:** `08-ticketing-spec.md`
**Status:** 🟡 Draft — pending PRD update
**Last updated:** 2026-03-22

---

## 1. Why This Document Exists

The original audit (`03-database-audit.md`) correctly identified the EventPrime ticketing
architecture, including `em_price_options` (tickets), `eventprime_ticket_categories`
(ticket category groups), and the parent/child relationship between them via
`parent_price_option_id` and `category_id`.

The PRD (`06-prd.md`) did not translate this architecture into the rebuild spec.
Instead it reduced ticketing to two simple post meta fields (`dish_price`,
`dish_special_price`) on the class. This was the root cause of the mid-build rework.

This document captures the confirmed ticketing design so it can be used to:
- Update the PRD before further build work resumes
- Drive the DB schema and admin UX build
- Serve as the source of truth going forward

---

## 2. Core Concept

Tickets are **global reusable templates**, not per-class records.

A class does not own its ticket data — it references a ticket type by ID. This means:

- Ticket types are created and managed once in a dedicated admin interface
- Changing a ticket type (e.g. updating a price) propagates to all future classes using it
- Bookings record the **price at time of purchase** separately (immutable record)
- A class stores only a single `dish_ticket_type_id` foreign key as post meta

This is **Option A (live link)** from the design discussion — confirmed correct for
Dish's use case where the same formats run repeatedly.

---

## 3. Hierarchy

```
Ticket Category  (global template — organisational label only)
└── Ticket Type  (global template — all pricing/capacity/fee detail)
    └── dish_class (post meta: dish_ticket_type_id → FK to ticket type)
```

### Examples

```
Hands On
├── German Beer Garden
├── Maritime Kitchen Party
└── Italian American Night

Skills Class
├── Knife Skills
└── Pasta Making

Couples Night
└── Date Night Cook-Off
```

A class references exactly **one ticket type**. No mixing of types per class.

---

## 4. Confirmed Field Spec

### 4a. Ticket Category

Purely organisational. No pricing or capacity at this level.

| Field        | DB Column     | Type          | Notes                    |
|---|---|---|---|
| Name         | `name`        | varchar(255)  | e.g. "Hands On"          |
| Description  | `description` | text          | Optional internal note   |
| Active       | `is_active`   | tinyint(1)    | Soft delete              |
| Sort order   | `priority`    | int(11)       |                          |
| Created at   | `created_at`  | datetime      |                          |
| Updated at   | `updated_at`  | datetime      |                          |

---

### 4b. Ticket Type

All pricing, capacity, and booking window configuration lives here.

#### Core fields

| Field                        | DB Column              | Type         | Notes                                      |
|---|---|---|---|
| Name                         | `name`                 | varchar(255) | e.g. "German Beer Garden"                  |
| Category                     | `category_id`          | int(11)      | FK → dish_ticket_categories.id             |
| Description                  | `description`          | text         | Optional — customer-facing or internal TBD |
| Capacity                     | `capacity`             | int(11)      | Total seats; remaining calculated at runtime |
| Show remaining count to users| `show_remaining`       | tinyint(1)   | Frontend "X spots left" display            |
| Price (cents)                | `price_cents`          | int(11)      | Required                                   |
| Sale price (cents)           | `sale_price_cents`     | int(11)      | NULL = no sale; silently replaces price on frontend |
| Min per booking              | `min_per_booking`      | int(11)      | Default 1; enforces e.g. couples minimum   |
| Active                       | `is_active`            | tinyint(1)   | Soft delete; inactive = hidden in dropdowns |
| Sort order                   | `priority`             | int(11)      |                                            |
| Created at                   | `created_at`           | datetime     |                                            |
| Updated at                   | `updated_at`           | datetime     |                                            |

#### Availability window

Stored as a JSON object in a single `booking_starts` column.

One of three modes — **mutually exclusive**:

| Mode            | Value stored                             |
|---|---|
| Right away      | `{"mode": "immediate"}`                 |
| N days before   | `{"mode": "days_before", "days": 30}`   |
| Specific date   | `{"mode": "date", "date": "2026-05-01"}`|

Show availability dates toggle:

| Field                         | DB Column                | Type       | Notes                         |
|---|---|---|---|
| Show availability dates on frontend | `show_booking_dates` | tinyint(1) | When 0, no date info shown to users |

#### Per-ticket fees (repeater)

Multiplied by ticket quantity at checkout. Stored as JSON array in `per_ticket_fees`.

```json
[
  {"label": "Kitchen Supply Fee", "amount_cents": 500},
  {"label": "Equipment Hire",     "amount_cents": 1000}
]
```

#### Per-booking fees (repeater)

Flat charge once per booking regardless of quantity. Stored as JSON array in `per_booking_fees`.

```json
[
  {"label": "Corkage Fee",  "amount_cents": 1500},
  {"label": "Booking Fee",  "amount_cents": 200}
]
```

---

### 4c. dish_class post meta changes

| Meta Key             | Status   | Notes                                           |
|---|---|---|
| `dish_price`         | ❌ Remove | Moves to ticket type                           |
| `dish_special_price` | ❌ Remove | Moves to ticket type (sale_price_cents)        |
| `dish_event_fee`     | ❌ Remove | Replaced by per-booking fees repeater          |
| `dish_capacity`      | ❌ Remove | Moves to ticket type                           |
| `dish_ticket_type_id`| ✅ Add    | int — FK to dish_ticket_types.id               |
| `dish_booking_opens` | ✅ Keep   | Per-class booking window open datetime         |
| `dish_booking_closes`| ✅ Keep   | Per-class booking window close datetime        |

> **Note:** `dish_booking_opens` / `dish_booking_closes` on the class are a *secondary*
> override. The ticket type's `booking_starts` config is the primary availability rule.
> Relationship between the two needs to be resolved before the booking engine is built
> (Phase 9). For now, keep both and document the conflict.

---

## 5. DB Schema

### `dish_ticket_categories`

```sql
CREATE TABLE {prefix}dish_ticket_categories (
  id          bigint(20)   NOT NULL AUTO_INCREMENT,
  name        varchar(255) NOT NULL DEFAULT '',
  description text,
  is_active   tinyint(1)   NOT NULL DEFAULT 1,
  priority    int(11)      NOT NULL DEFAULT 0,
  created_at  datetime     NOT NULL,
  updated_at  datetime              DEFAULT NULL,
  PRIMARY KEY  (id)
);
```

### `dish_ticket_types` (replaces `dish_tickets`)

```sql
CREATE TABLE {prefix}dish_ticket_types (
  id                  bigint(20)   NOT NULL AUTO_INCREMENT,
  category_id         int(11)      NOT NULL DEFAULT 0,
  name                varchar(255) NOT NULL DEFAULT '',
  description         text,
  capacity            int(11)               DEFAULT NULL,
  show_remaining      tinyint(1)   NOT NULL DEFAULT 0,
  price_cents         int(11)      NOT NULL DEFAULT 0,
  sale_price_cents    int(11)               DEFAULT NULL,
  min_per_booking     int(11)      NOT NULL DEFAULT 1,
  per_ticket_fees     longtext              DEFAULT NULL,
  per_booking_fees    longtext              DEFAULT NULL,
  show_booking_dates  tinyint(1)   NOT NULL DEFAULT 0,
  booking_starts      longtext              DEFAULT NULL,
  is_active           tinyint(1)   NOT NULL DEFAULT 1,
  priority            int(11)      NOT NULL DEFAULT 0,
  created_at          datetime     NOT NULL,
  updated_at          datetime              DEFAULT NULL,
  PRIMARY KEY  (id),
  KEY category_id (category_id)
);
```

---

## 6. Admin UX

### New "Ticketing" menu group

Sits under the Dish Events admin menu:

```
Dish Events
├── Classes
├── Chefs
├── Bookings
├── Ticketing               ← new top-level submenu item
│   ├── Ticket Types        ← default landing page (WP_List_Table)
│   └── Categories          ← (WP_List_Table)
└── Settings
```

### Ticket Types list table columns

| Column        | Notes                                 |
|---|---|
| Name          | Links to edit screen                  |
| Category      | Parent category name                  |
| Price         | Formatted dollar amount               |
| Sale Price    | Formatted, or "—"                     |
| Capacity      | Integer                               |
| Active        | Toggle                                |

### Class edit screen — Tickets tab (revised)

Remove: all inline price/capacity/fee fields and the ticket repeater.

Replace with:

1. **Category** dropdown — populated from active `dish_ticket_categories` records
2. **Ticket Type** dropdown — populated from active `dish_ticket_types` filtered by
   selected category (JS-driven progressive disclosure)
3. **Read-only summary card** — once a type is selected, display its price, capacity,
   fees, and availability window so the editor can confirm the selection
4. **Booking Opens** datetime — per-class override (keep existing field)
5. **Booking Closes** datetime — per-class override (keep existing field)

---

## 7. Open Questions (resolve before Phase 9 — Booking Engine)

| # | Question |
|---|---|
| 1 | When both `dish_booking_opens` (on class) and `booking_starts` (on ticket type) are set, which takes precedence? Proposed: ticket type wins; class-level fields act as a hard outer boundary. |
| 2 | Description field on ticket type — customer-facing (shown at checkout) or internal admin note, or both with separate fields? |
| 3 | Are sale prices ever time-bounded (early bird pricing with an end date)? Not in scope yet, but worth flagging. |
| 4 | When a booking is created, the price at time of purchase must be stored on the booking record. Confirm `dish_total_cents` on `dish_booking` post meta is sufficient, or does a line-item breakdown need storing? |

---

## 8. What Needs Updating in Other Docs

| Document       | Update required                                                               |
|---|---|
| `06-prd.md`    | §4a: replace `dish_price`, `dish_special_price`, `dish_capacity` post meta with `dish_ticket_type_id`; add §4d Ticket Types and §4e Ticket Categories data model |
| `06-prd.md`    | §4c (DB tables): rename `dish_tickets` → `dish_ticket_types`; update schema; update `dish_ticket_categories` schema to remove `event_id` (categories are now global) |
| `07-architecture.md` | Add `Ticketing` admin module to the admin layer diagram |
| `06-prd.md`    | §6 (Admin): document the new Ticketing menu group and revised Tickets tab UX |
