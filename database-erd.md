# Archery Performance OS — Database ERD

## Overview

Two separate database scopes:

- **Central DB** — shared across all tenants (platform-level data)
- **Tenant DB** — one per club (all club-specific data)

No foreign-key constraints cross database boundaries. Cross-DB references (e.g. `archers.user_id → users.id`) are enforced at the application layer only.

---

## Central Database

```
┌──────────────────────────────────────────────────────────────────────────┐
│  CENTRAL DATABASE  (e.g. fmsport_archery)                                │
│  Laravel connection: mysql                                               │
└──────────────────────────────────────────────────────────────────────────┘

┌─────────────────────┐        ┌──────────────────────┐        ┌────────────────────┐
│       tenants        │        │     tenant_user       │        │       users        │
├─────────────────────┤        ├──────────────────────┤        ├────────────────────┤
│ id           PK     │◄───────│ id           PK      │───────►│ id          PK     │
│ name                │  1   N │ tenant_id    FK       │  N   1 │ name               │
│ slug         UQ     │        │ user_id      FK       │        │ email       UQ     │
│ db_name      UQ     │        │ role                  │        │ password           │
│ db_host             │        │   club_admin          │        │ is_super_admin     │
│ db_username         │        │   coach               │        │ email_verified_at  │
│ db_password (enc.)  │        │   archer              │        │ remember_token     │
│ plan                │        │ created_at            │        │ created_at         │
│   free | starter    │        │ updated_at            │        │ updated_at         │
│   pro | enterprise  │        │ UQ(tenant_id,user_id) │        └────────────────────┘
│ status              │        └──────────────────────┘
│   active            │
│   suspended         │        ┌──────────────────────┐        ┌──────────────────────────┐
│   cancelled         │        │    tenant_billing     │        │  password_reset_tokens   │
│ created_at          │◄───────│ id           PK      │        ├──────────────────────────┤
│ updated_at          │  1   1 │ tenant_id    FK(UQ)   │        │ email        PK          │
└─────────────────────┘        │ plan                  │        │ token                    │
                               │ status                │        │ created_at               │
                               │   active | trialing   │        └──────────────────────────┘
                               │   past_due | cancelled│
                               │   incomplete          │        ┌──────────────────────┐
                               │ renews_at             │        │      sessions        │
                               │ created_at            │        ├──────────────────────┤
                               │ updated_at            │        │ id           PK      │
                               └──────────────────────┘        │ user_id      FK(idx) │
                                                               │ ip_address           │
── Cashier (Stripe) tables ──────────────────────────────────  │ user_agent           │
                                                               │ payload              │
┌──────────────────────┐        ┌──────────────────────────┐  │ last_activity        │
│    subscriptions     │        │   subscription_items     │  └──────────────────────┘
├──────────────────────┤        ├──────────────────────────┤
│ id           PK      │◄───────│ id              PK       │
│ user_id      FK      │  1   N │ subscription_id FK       │  ┌──────────────────────────────┐
│ type                 │        │ stripe_id       UQ       │  │  Spatie Permission Tables    │
│ stripe_id    UQ      │        │ stripe_product           │  │  (Central scope)             │
│ stripe_status        │        │ stripe_price             │  ├──────────────────────────────┤
│ stripe_price         │        │ quantity                 │  │  permissions                 │
│ quantity             │        │ created_at               │  │  roles                       │
│ trial_ends_at        │        │ updated_at               │  │  model_has_permissions       │
│ ends_at              │        └──────────────────────────┘  │  model_has_roles             │
│ created_at           │                                       │  role_has_permissions        │
│ updated_at           │                                       └──────────────────────────────┘
└──────────────────────┘

  users also have Cashier columns (customer_columns migration):
    stripe_id | pm_type | pm_last_four | trial_ends_at
```

---

## Tenant Database (per club)

```
┌──────────────────────────────────────────────────────────────────────────────┐
│  TENANT DATABASE  e.g. fmsport_riverdale                                     │
│  Laravel connection: tenant (auto-resolved by Stancl Tenancy middleware)     │
│  No FK constraints back to central DB — cross-DB refs enforced in app code.  │
└──────────────────────────────────────────────────────────────────────────────┘

── Core People ─────────────────────────────────────────────────────────────────

  ┌──────────────────────────────┐
  │           coaches            │
  ├──────────────────────────────┤
  │ id              PK           │
  │ user_id*        UQ idx       │  ← references central users.id (no DB FK)
  │ level           nullable     │  e.g. Level 1 | Level 2 | Master Coach
  │ notes           text nullable│
  │ created_at                   │
  │ updated_at                   │
  └──────────────┬───────────────┘
                 │ 1
                 │
                 │ N
  ┌──────────────▼───────────────────────────────────────┐
  │                       archers                        │
  ├──────────────────────────────────────────────────────┤
  │ id              PK                                   │
  │ user_id*        nullable idx   ← central users.id   │
  │ coach_id        FK nullable    → coaches.id          │
  │ category        nullable       U12|U15|U18|U21|Senior│
  │                                Master                │
  │ date_of_birth   date nullable                        │
  │ dominant_hand   varchar(10) nullable  right|left     │
  │ phone           varchar(30) nullable                 │
  │ created_at                                           │
  │ updated_at                                           │
  └──────┬──────────────────────────────────────────────┘
         │ 1
         ├─────────────────────────────────────────────────────────┐
         │                       │                 │               │
         │ N                     │ N               │ N             │ N
         ▼                       ▼                 ▼               ▼

── Training & Live Scoring ─────────────────────────────────────────────────────

  ┌────────────────────────┐
  │   training_sessions    │
  ├────────────────────────┤
  │ id           PK        │
  │ archer_id    FK        │ → archers.id (cascade delete)
  │ coach_id     FK null   │ → coaches.id (null on delete)
  │ round_type   nullable  │ WA 18 | WA 25 | Portsmouth | Practice…
  │ distance_metres u16 ?  │
  │ max_score    u16 ?     │
  │ started_at   timestamp │ default CURRENT_TIMESTAMP
  │ ended_at     timestamp?│
  │ notes        text ?    │
  │ created_at             │
  │ updated_at             │
  │ IDX(archer_id,started) │
  └──────────┬─────────────┘
             │ 1
             │
             │ 1
  ┌──────────▼─────────────┐
  │     live_sessions      │
  ├────────────────────────┤
  │ id           PK        │
  │ training_session_id FK │ → training_sessions.id (cascade delete)
  │ status                 │ active | completed
  │ arrows_per_end u8      │ default 6
  │ started_at   timestamp │
  │ ended_at     timestamp?│
  │ created_at             │
  │ updated_at             │
  │ IDX(ts_id, status)     │
  └──────────┬─────────────┘
             │ 1
             │
             │ N
  ┌──────────▼─────────────┐
  │       live_ends        │
  ├────────────────────────┤
  │ id           PK        │
  │ live_session_id FK     │ → live_sessions.id (cascade delete)
  │ end_number   u8        │ ordinal within session
  │ total_score  u16       │ computed from arrows
  │ x_count      u8        │
  │ ten_count    u8        │
  │ tag          varchar ? │ e.g. "windy", "fatigue"
  │ notes        text ?    │
  │ created_at             │
  │ updated_at             │
  └──────────┬─────────────┘
             │ 1
             │
             │ N
  ┌──────────▼─────────────┐
  │      live_arrows       │
  ├────────────────────────┤
  │ id           PK        │
  │ live_end_id  FK        │ → live_ends.id (cascade delete)
  │ arrow_number u8        │ position within end
  │ score        varchar   │ X | 10 | 9 | … | 1 | M
  │ position_x   dec(6,3)? │ target face X coordinate
  │ position_y   dec(6,3)? │ target face Y coordinate
  │ created_at             │
  │ updated_at             │
  └────────────────────────┘

── Equipment ───────────────────────────────────────────────────────────────────

  archers (1) ────────────────────────────────────────────────── (N) ▼

  ┌─────────────────────────────────────┐
  │          equipment_setups           │
  ├─────────────────────────────────────┤
  │ id              PK                  │
  │ archer_id       FK                  │ → archers.id (cascade delete)
  │ name            varchar             │ e.g. "Indoor Recurve"
  │ bow_type        varchar             │ Recurve|Compound|Barebow|Longbow
  │ bow_brand       nullable            │
  │ bow_model       nullable            │
  │ draw_weight_lbs dec(5,1) ?          │
  │ draw_length_inches dec(4,1) ?       │
  │ arrow_brand     nullable            │
  │ arrow_model     nullable            │
  │ arrow_spine     u16 ?               │
  │ is_current      bool default false  │
  │ created_at                          │
  │ updated_at                          │
  │ IDX(archer_id, is_current)          │
  └──────────────┬──────────────────────┘
                 │ 1
                 │
                 │ N
  ┌──────────────▼──────────────────────┐
  │       equipment_maintenances        │
  ├─────────────────────────────────────┤
  │ id                   PK             │
  │ equipment_setup_id   FK             │ → equipment_setups.id (cascade delete)
  │ type                 enum           │ check|repair|replacement|tuning|cleaning
  │ description          varchar        │
  │ details              text ?         │
  │ cost                 dec(8,2) ?     │
  │ performed_at         date           │
  │ next_due_at          date ?         │
  │ performed_by         varchar ?      │ free-text name/shop
  │ created_at                          │
  │ updated_at                          │
  │ IDX(equipment_setup_id,performed_at)│
  └─────────────────────────────────────┘

── Competitions ────────────────────────────────────────────────────────────────

  archers (1) ─────────────────────────────────────────────────── (N) ▼

  ┌──────────────────────────┐         ┌─────────────────────────────┐
  │       competitions       │         │     competition_results      │
  ├──────────────────────────┤         ├─────────────────────────────┤
  │ id            PK         │◄────────│ id              PK           │
  │ name                     │  1   N  │ competition_id  FK           │ → competitions.id
  │ location      nullable   │         │ archer_id       FK           │ → archers.id
  │ date          date       │         │ category        nullable     │ e.g. Senior Recurve
  │ level         nullable   │         │ score           u16 ?        │
  │   club|regional|national │         │ max_score       u16 ?        │
  │   international          │         │ placing         u16 ?        │ 1=gold, 2=silver…
  │ round_type    nullable   │         │ competed_at     date ?       │
  │ distance_metres u16 ?    │         │ notes           text ?       │
  │ created_at               │         │ created_at                   │
  │ updated_at               │         │ updated_at                   │
  │ IDX(date)                │         │ UQ(competition_id,archer_id) │
  └──────────────────────────┘         │ IDX(archer_id,competed_at)   │
                                       └─────────────────────────────┘

── Lanes & Bookings ────────────────────────────────────────────────────────────

  ┌────────────────────────┐         ┌──────────────────────────────────────┐
  │         lanes          │         │           lane_bookings              │
  ├────────────────────────┤         ├──────────────────────────────────────┤
  │ id          PK         │◄────────│ id           PK                      │
  │ number      u8 UQ      │  1   N  │ lane_id      FK                      │ → lanes.id (cascade)
  │ name        varchar    │         │ archer_id    FK nullable              │ → archers.id (set null)
  │ distance_metres u16    │         │ group_id     u64 nullable            │ reserved for future
  │   default 18           │         │ start_time   dateTime                │
  │ target_face varchar(10)│         │ end_time     dateTime                │
  │   40cm|60cm|80cm|122cm │         │ purpose      varchar ?               │
  │ is_active   bool       │         │   training|competition warmup        │
  │ created_at             │         │   coaching                           │
  │ updated_at             │         │ created_at                           │
  └────────────────────────┘         │ updated_at                           │
                                     │ IDX(lane_id,start_time,end_time)     │
                                     └──────────────────────────────────────┘

── Group Sessions & Attendance ─────────────────────────────────────────────────

  coaches (1) ─────────────────────────────────────────────────── (N) ▼

  ┌──────────────────────────────┐
  │        group_sessions        │
  ├──────────────────────────────┤
  │ id              PK           │
  │ coach_id        u64 nullable │  ← coaches.id (no DB FK — tenant internal)
  │ title           varchar      │
  │ type            enum         │ technique|fitness|competition_prep
  │                              │ beginner|general
  │ scheduled_at    dateTime     │
  │ duration_minutes u16 def 90  │
  │ location        nullable     │
  │ notes           text ?       │
  │ status          enum         │ scheduled|completed|cancelled
  │ created_at                   │
  │ updated_at                   │
  │ IDX(scheduled_at)            │
  │ IDX(coach_id)                │
  └──────────────┬───────────────┘
                 │ 1
                 │
                 │ N
  ┌──────────────▼───────────────┐
  │          attendances         │
  ├──────────────────────────────┤
  │ id                  PK       │
  │ group_session_id    FK       │ → group_sessions.id (cascade delete)
  │ archer_id           FK       │ → archers.id (cascade delete)
  │ status              enum     │ present|absent|late|excused
  │ notes               text ?   │
  │ marked_at           timestamp?│
  │ created_at                   │
  │ updated_at                   │
  │ UQ(group_session_id,archer_id)│
  │ IDX(archer_id)               │
  └──────────────────────────────┘

── Coach Notes ─────────────────────────────────────────────────────────────────

  ┌──────────────────────────────┐
  │         coach_notes          │
  ├──────────────────────────────┤
  │ id          PK               │
  │ archer_id   FK               │ → archers.id (cascade delete)
  │ coach_id    FK               │ → coaches.id (cascade delete)
  │ content     text             │
  │ created_at                   │
  │ updated_at                   │
  │ IDX(archer_id, coach_id)     │
  └──────────────────────────────┘

── Spatie Permissions (Tenant scope) ───────────────────────────────────────────

  ┌──────────────────────────────────┐
  │    Spatie Permission Tables      │
  │    (replicated into every tenant │
  │    DB via tenant migration)      │
  ├──────────────────────────────────┤
  │ roles          club_admin        │
  │                coach             │
  │                archer            │
  │ permissions                      │
  │ model_has_roles                  │
  │ model_has_permissions            │
  │ role_has_permissions             │
  └──────────────────────────────────┘
```

---

## Relationships Summary

### Central DB

| Table | Relationship | Table |
|-------|-------------|-------|
| `tenants` | has many (pivot) | `users` via `tenant_user` |
| `tenants` | has one | `tenant_billing` |
| `users` | belongs to many | `tenants` via `tenant_user` |
| `users` | has many | `subscriptions` (Cashier) |
| `subscriptions` | has many | `subscription_items` |

### Tenant DB

| Table | Relationship | Table |
|-------|-------------|-------|
| `coaches` | has many | `archers` |
| `coaches` | has many | `training_sessions` |
| `coaches` | has many | `coach_notes` |
| `coaches` | has many | `group_sessions` |
| `archers` | belongs to | `coaches` (optional) |
| `archers` | has many | `training_sessions` |
| `archers` | has many | `equipment_setups` |
| `archers` | has many | `competition_results` |
| `archers` | has many | `lane_bookings` |
| `archers` | has many | `coach_notes` |
| `archers` | has many (pivot) | `group_sessions` via `attendances` |
| `equipment_setups` | has many | `equipment_maintenances` |
| `training_sessions` | has one | `live_sessions` |
| `live_sessions` | has many | `live_ends` |
| `live_ends` | has many | `live_arrows` |
| `competitions` | has many | `competition_results` |
| `lanes` | has many | `lane_bookings` |
| `group_sessions` | has many (pivot) | `archers` via `attendances` |

---

## Cross-Database Reference Map

| Tenant column | References | Enforcement |
|---------------|-----------|-------------|
| `archers.user_id` | `central.users.id` | App-level only (no DB FK) |
| `coaches.user_id` | `central.users.id` | App-level only (no DB FK) |
| `group_sessions.coach_id` | `tenant.coaches.id` | App-level only (no DB FK) |
| `lane_bookings.group_id` | *(future groups table)* | Reserved, not yet used |

---

## Notes

- **Name resolution**: `archers` and `coaches` have no `name` column — display names come from `central.users.name` via the cross-DB `user_id` reference.
- **Spatie Permissions** tables exist in **both** central and tenant DBs. Tenant roles (`club_admin`, `coach`, `archer`) are managed in the tenant DB. `TenantRoleMiddleware` temporarily switches the User model connection to `tenant` before checking `hasAnyRole()`.
- **Cashier** (`subscriptions`, `subscription_items`, customer columns on `users`) is managed by Laravel Cashier and lives in the central DB.
- **`tenant_user.role`** is the coarse role used for routing decisions at login. Spatie roles in the tenant DB are the authoritative source for permission checks inside the tenant app.
- **`equipment_maintenances.performed_by`** is free text — it represents a person or shop name, not a FK to any user.
