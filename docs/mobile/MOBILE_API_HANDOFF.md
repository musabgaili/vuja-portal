# Vuja Portal — Mobile App Build: Session Handoff

> **Purpose of this file.** Everything a fresh session needs to resume the Vuja Portal
> mobile-app work without any prior conversation. Read this top-to-bottom, then read
> `docs/mobile/AUDIT.md` (the full Phase 0 audit) for deep reference.
>
> **Last updated:** 2026-07-04 · **Branch:** `mobile-api` · **Working tree:** clean, all committed.

---

## 0. TL;DR — you are here

We are building a **production Flutter mobile app** (Android + iOS, **not** a webview) for the
existing **Vuja Portal** Laravel web app. **One role-switched app** serves **both** external
*clients* and internal *staff*. Work is split in phases; we are in **Phase 1 = building the
additive backend mobile API** (`/api/v1`, Sanctum tokens). **Flutter (Phase 2) has not started**
(blocked: Flutter SDK not installed).

**Done so far on the `mobile-api` branch (pushed to `origin/mobile-api`, not merged to main):**
- Phase 0 audit (`docs/mobile/AUDIT.md`, 1,571 lines).
- Phase 1 foundation: Accept-Language locale middleware + 30-day Sanctum token TTL.
- Phase 1 client endpoints: **Profile** (full CRUD), **Client Dashboard**, and **My Requests**
  unified feed (`GET /api/v1/requests`) — the "My Requests" open decision was resolved to the
  **single unified feed**; aggregation extracted to `ClientRequestsService` (shared web+API).

**Next up:** the rest of the client-P0 queue — **Create request → Projects → Quotes → Invoices →
Engagement ledger → Meetings (book/cancel)** — then staff P0, then API docs/tests, then Flutter.

---

## 1. Hard constraints (do not violate)

These are the user's explicit, non-negotiable rules for this project:

1. **Additive backend only.** New API routes, new controllers under `Api\V1`, new middleware,
   new services. **Zero breaking changes** to existing web routes, web controllers, or Blade
   views. The web app must keep working exactly as-is.
   - *Allowed exception, used once:* behaviour-preserving **extraction** of controller logic into
     a shared Service that both web + API call — but only if the web behaviour is unchanged and
     you add a test that proves it. (We did this once for the dashboard stats; see §5.)
2. **Read before you write. Never invent, assume, or approximate.** Every value (validation rule,
   status enum, field name) must come from the actual codebase. If ambiguous, ask.
3. **Reuse, don't duplicate.** Reuse existing FormRequests/Services for validation & business
   logic. If logic lives inline in a controller, extract to a shared Service rather than copy it.
4. **Not a webview.** Native mobile UX. Light & fast.
5. **Approved Flutter deps only** (see §8) — nothing else without asking.
6. **Phase by phase, stop-and-verify.** Build in small batches; at each checkpoint run the suite,
   confirm no new failures, commit, and summarise before continuing.
7. **Firebase is NOT available** → push notifications must be **stubbed** for now.

---

## 2. App identity (decided)

| Item | Value |
|---|---|
| Launcher name | **Vujà Dé** |
| Store listing | Vuja De Innovations |
| Package / bundle id | `com.vujade.portal` |
| Flutter project dir (planned) | `C:\Users\munzir.alradi\Documents\vuja_portal_mobile` |
| Audience | **both** client + staff, one app, role-switched by `user.type` |

---

## 3. Environment & how to run

- **Repo:** `C:\Users\munzir.alradi\Documents\Vuja Portal` → GitHub `github.com/musabgaili/vuja-portal`.
- **Stack:** Laravel 12, PHP 8.5.4 (running), Composer 2.9.5, **sqlite** DB, Sanctum 4.3,
  spatie/laravel-permission + medialibrary, laravel/socialite, Bootstrap (laravel/ui). Bilingual en/ar (RTL).
- **Serve web:** app runs at **http://localhost:8000**. Local login password for seeded users is
  **`12345678`** (not "password").
- **PHP 8.5 quirks:** `composer install` needs `--ignore-platform-req=php`; php.ini needs
  `error_reporting = E_ALL & ~E_DEPRECATED` to quiet the flood. Test output shows PHP-8.5
  `PDO::MYSQL_ATTR_*` **deprecation** notices — these are cosmetic, **not** failures.
- **Run tests:** `php artisan test` (phpunit, sqlite `:memory:`, `RefreshDatabase`).
- **⚠ Baseline test state (pre-existing, NOT ours):** the suite has **2 failing tests** in
  `tests/Feature/AuthTest.php` — `user can register with valid data` and `user can login with
  valid credentials`. They assert a redirect to `/home`, but the app correctly redirects to
  `/client/dashboard`. **Our guarantee is: no *new* failures beyond these two.** After our work
  the suite is: `2 failed, 1 passed, 37 deprecated` (119 assertions) — same 2 failures, plus our
  13 passing new tests (counted under "deprecated" because of the PHP-8.5 notices).

---

## 4. Git state (verified)

Branch **`mobile-api`**, clean tree. Commits ahead of `main` (newest first):

```
7404733  Add mobile API client dashboard + extract shared stats service
0063648  Add mobile API profile endpoints (client P0)
3241507  Phase 1 foundation: API Accept-Language locale middleware + 30-day Sanctum token TTL
6a0089d  Add Phase 0 mobile audit (docs/mobile/AUDIT.md)
```

The **base API** (login/me/logout, notifications, engagement, meetings) already existed on `main`
from a prior session. **Not yet merged to main.** Deploy later = `composer install` + `php artisan
migrate` (no new migrations added on this branch so far).

**Files touched on this branch (`git diff --stat main...HEAD`):**

```
app/Http/Controllers/Api/V1/DashboardController.php   +29   (new)
app/Http/Controllers/Api/V1/ProfileController.php     +106  (new)
app/Http/Controllers/DashboardController.php          81±   (web — behaviour-preserving refactor)
app/Http/Middleware/ApiLocalization.php               +32   (new)
app/Services/Dashboard/ClientDashboardService.php     +163  (new, shared web+API)
bootstrap/app.php                                     +5    (register api middleware)
config/sanctum.php                                    +2    (token TTL)
docs/mobile/AUDIT.md                                  +1571 (Phase 0)
routes/api.php                                         +13
tests/Feature/Api/DashboardApiTest.php                +41   (new)
tests/Feature/Api/ProfileApiTest.php                  +103  (new)
tests/Feature/ClientDashboardWebTest.php              +25   (new, guards the web refactor)
```

---

## 5. API architecture & conventions (follow these for every new endpoint)

- **Auth:** Laravel Sanctum **personal access tokens** (bearer). Guard config unchanged; token
  **TTL = 30 days** (`config/sanctum.php` → `'expiration' => 60 * 24 * 30`). Client stores the
  token in secure storage and on a **401 logs the user out**.
- **Routing:** everything under `routes/api.php`, prefix **`v1`**. Public: `login` (throttled
  `throttle:10,1`). Everything else inside `Route::middleware('auth:sanctum')->group(...)`.
- **JSON envelope:** `JsonResource::withoutWrapping()` is set in `AppServiceProvider` → single
  resources return a **flat object** (no `data` wrapper). Paginated collections still include
  `data` + `links`/`meta`. Ad-hoc payloads use plain `response()->json([...])`.
- **Errors are always JSON for `api/*`** (configured in `bootstrap/app.php` `withExceptions`):
  validation → **422** `{message, errors:{field:[...]}}`; unauth → **401**; forbidden → **403**.
  Never an HTML error page or login redirect. **Map 422 field errors back onto the exact form
  field names** on the client.
- **Localisation:** `App\Http\Middleware\ApiLocalization` reads `Accept-Language` (e.g.
  `ar-SA,ar;q=0.9` → `ar`) and sets the app locale per request (stateless). Registered on the
  `api` middleware group. Supported: `en`, `ar`. Use `__('...')` for user-facing strings.
- **Validation:** mirror the **web's exact rules**. If the web uses inline `$request->validate()`
  (many do), mirror those rules inline in the API controller. Prefer the `current_password:sanctum`
  rule for password re-checks (pin the **sanctum** guard — the default guard trick works but the
  explicit guard is robust in tests + requests).
- **Resources:** put response shaping in `app/Http/Resources/*`. Existing: `UserResource`,
  `MeetingResource` (shapes in §6). Add a Resource per new entity; keep pagination on list endpoints.
- **Shared logic:** business/read logic that the web and API both need goes in `app/Services/...`.
  Example done: `app/Services/Dashboard/ClientDashboardService::stats()` holds the client home
  counters; **both** the web `DashboardController` and the API now call it, so numbers can't drift.
  The web refactor is guarded by `tests/Feature/ClientDashboardWebTest.php`.

---

## 6. Current API surface (15 routes, all verified via `route:list`)

### Existing (already on `main`)

| Method | Path | Controller | Contract |
|---|---|---|---|
| POST | `/api/v1/login` | `AuthController@login` | body `{email, password, device_name?}` → `{token, user:UserResource}`. Bad creds → 422 generic `email` error (no user enumeration). Suspended/inactive → 422 "This account is not active." Public + `throttle:10,1`. |
| GET | `/api/v1/me` | `AuthController@me` | → `UserResource` (flat). |
| POST | `/api/v1/logout` | `AuthController@logout` | revokes **only the current** token → `{message}`. |
| GET | `/api/v1/notifications?limit=20` | `NotificationController@index` | limit clamped 1–50 → `{unread_count, items:[...]}` (from `NotificationService`). |
| POST | `/api/v1/notifications/seen` | `NotificationController@seen` | clears unread badge → `{ok:true}`. |
| GET | `/api/v1/engagement` | `EngagementController@me` | → `{impact_points, level_index, progress}`; internal staff who hold targets also get `targets_attainment` (int %). |
| GET | `/api/v1/meetings` | `MeetingController@index` | paginated `MeetingResource` collection (`data` + pagination meta) via `MeetingService::getUserMeetings`. |
| GET | `/api/v1/meetings/{meeting}` | `MeetingController@show` | uuid route key. 403 unless client/team-member/manager/PM/attendee. → `MeetingResource`. |

### Added on `mobile-api`

| Method | Path | Controller | Contract |
|---|---|---|---|
| GET | `/api/v1/dashboard` | `DashboardController@client` | → `{stats:{...9 counters...}, recent_activity:[], active_projects:[]}`. Scoped to caller. Uses `ClientDashboardService`. |
| GET | `/api/v1/profile` | `ProfileController@show` | → `UserResource`. |
| PUT | `/api/v1/profile` | `ProfileController@update` | `{name(required), phone?(digits only, max 20)}` → `UserResource`. |
| PUT | `/api/v1/profile/email` | `ProfileController@updateEmail` | `{email(unique), current_password}` → `{message, user:UserResource}`. Sets `email_verified_at=null` via **forceFill** + resends verification. |
| PUT | `/api/v1/profile/password` | `ProfileController@updatePassword` | `{current_password, password, password_confirmation}` (`Password::defaults()`) → `{message}`. |
| PUT | `/api/v1/profile/phone` | `ProfileController@updatePhone` | `{phone?(digits only, max 20)}` → `UserResource`. |
| DELETE | `/api/v1/profile` | `ProfileController@destroy` | `{password, confirmation:"DELETE"}` → `{message}`. Revokes **all** tokens then deletes the account. |

**`stats` keys (all `int`):** `active_projects, pending_projects, completed_projects,
requests_in_review, requests_approved, meetings_this_week, meetings_today, total_tokens,
ai_assessments`. `recent_activity` / `active_projects` items:
`{type, id, title, status, status_label, updated_at(ISO)}`.

**`UserResource`:** `{id, name, email, phone, role, type(internal|client), status, is_internal,
is_client, is_manager, is_project_manager, impact_points}`.

**`MeetingResource`:** `{id(uuid), title, description, notes, status, status_label,
scheduled_at(ISO), ends_at(ISO), duration_minutes, meeting_link, with:{name,email,phone}|null,
attendees?[{name,status}], can_confirm, can_complete}`.

---

## 7. The web bug we found (left unfixed — flag for a separate task)

`app/Http/Controllers/ProfileController.php::updateEmail` does
`$user->update([... 'email_verified_at' => null])`, **but `email_verified_at` is not in the User
model's `$fillable`**, so mass-assignment **silently drops it**. Net effect: **changing your email
on the website does NOT actually reset verification** (despite the "Require re-verification"
comment). The mobile API does it correctly with `forceFill`. Fixing the web side is out of scope
for the additive mobile work — raise it as its own small fix.

---

## 8. Reference for when Flutter (Phase 2) starts

**Approved deps (only these without asking):** feature-first + **Riverpod** (code-gen),
`go_router`, `dio`, `freezed` + `json_serializable`, `flutter_secure_storage`,
`flutter_localizations` + `intl`, `google_fonts`, `cached_network_image`, `skeletonizer`,
`connectivity_plus`, flavors via `--dart-define-from-file`, `very_good_analysis`. Material 3 with a
manual `ColorScheme`. Push = stubbed (no Firebase).

**Design tokens (from `public/css/app.css :root`, full detail in AUDIT §0.3):**
- Primary `#0C7075`; dark `#095055`; bright `#0F969C`; slate/secondary `#294D61`;
  accent `#6DA5C0`; light `#ddf0f1`.
- Header gradient `linear-gradient(135deg, #0F969C 0%, #0C7075 55%, #294D61 100%)`.
- Status: success `#16a34a`, warning `#d97706`, error `#dc2626`, info `#0891b2`.
  bg-secondary `#f1f6f6`.
- Fonts: **Almarai** (primary) + **Tajawal** (both render Arabic + Latin), via Google Fonts.
- Radius: sm `0.375rem` → 2xl `1.5rem`; cards use radius-xl + shadow-md.
- Default locale `en`; RTL via `[dir="rtl"]`; **Western numerals in both locales**.
- Logos in `public/images/`: `vd-logo-dark-trimmed.png` (preferred), `vd-logo-dark.png`,
  `vd-logo-light.png`, `favicon.svg`.

**Auth / roles model:** `user.type` (`internal` | `client`) decides staff vs client app surface;
`user.role` enum = `client | employee | manager | project_manager`. spatie roles/permissions are
seeded but **not enforced on routes** — the real gates are middleware aliases `is_internal`,
`is_manager`, `can_manage_projects`. No dedicated "admin" (manager is top tier). `UserResource`
already exposes the `is_*` booleans for the client to branch UI.

---

## 9. Remaining work (the plan)

### Phase 1 — client P0 API (in progress)

Done: Auth ✅, Profile ✅, Client dashboard ✅, Notifications ✅ (push TBD), Engagement snapshot ✅,
Meetings read ✅. **Still to build (in this order):**

| # | Feature | Web source | Notes |
|---|---|---|---|
| 3 | ~~**My Requests** — unified feed~~ ✅ **DONE** | `client.requests` | `GET /api/v1/requests` — 6 lines merged (idea/consultation/research/ip/copyright/threed; **prototypes have their own list**, mirroring the web). Shared `ClientRequestsService`. |
| 4 | **Create a service request** (core lines intake) | `ideas/consultations/...` controllers | reuse existing FormRequests/validation. **← next** |
| 5 | **Projects** — list, detail, milestone approve, docs/deliverables, comments, feedback | `projects.client.*` | |
| 6 | **Quotes** — list, view, accept/reject, download | `quotes.client.*` | |
| 7 | **Invoices** — list, view, upload receipt | `invoices.client.*` | file upload. |
| 8 | **Engagement Points** — full ledger, tiers, redeem | `engagement.client.*` | today only a snapshot exists; add ledger/redeem. |
| 9 | **Meetings — book + cancel** | `meetings` (client) | read exists; add write actions. |

**⚠ OPEN DECISION (item 3):** how to shape "My Requests" given the 7 separate models. Recommended
default = **one unified paginated feed** merging all types, each item tagged with `type`, plus a
type filter (standard mobile pattern, matches the audit). Alternative = per-service-line endpoints.
Confirm before building.

### Phase 1 — staff P0 API (task #32, after client set)
Internal dashboard, approvals, my-targets/capacity, staff meetings, chat-lite — **role-gated** to
`is_internal`. `GET /api/v1/dashboard` will branch on `user.type` (internal → staff payload) — an
additive change, not a rename.

### Phase 1 — cross-cutting (task #30 / #33)
- **Auth hardening:** mobile **password-reset** endpoints; **device-registration** endpoint
  (stub, since Firebase off). (`login` already accepts `device_name`.)
- **API docs** (`docs/mobile/API.md` or Scribe) + a **seeder per role** for QA + feature tests;
  keep the full suite green vs baseline.

### Phase 2 — Flutter (task #34) — BLOCKED
Flutter SDK is **not installed / not on PATH**. Install Flutter first, then scaffold the app
(feature-first + Riverpod, flavors, Material 3 with the tokens above), wire `dio` to `/api/v1`,
secure-storage token, go_router with role-based shells.

---

## 10. Testing conventions (learned)

- `RefreshDatabase`, sqlite `:memory:`. Auth in tests: `Laravel\Sanctum\Sanctum::actingAs($user)`.
- **Only `UserFactory` exists** — there are **no factories for the 7 request models** yet. For
  endpoints that aggregate/list those, either (a) test the empty-state contract, or (b) build the
  needed factories (preferred as you add My Requests/Projects). The `User` factory defaults to a
  **verified client** (`type=client`, `role=client`, `email_verified_at=now()`, password
  `"password"`); its `configure()` only flips `type→internal` when `role` is internal.
- `current_password` in tests works because `Sanctum::actingAs` sets the default guard to sanctum;
  we still pin `current_password:sanctum` explicitly.
- When you change a web controller (only via sanctioned extraction), **add a web smoke test** that
  proves the page still renders (see `ClientDashboardWebTest`).

---

## 11. Pointers

- **Full audit:** `docs/mobile/AUDIT.md` — §0.1 functional inventory, §0.2 data model + ERD,
  §0.3 design system, §0.4 roles/permissions, §0.5 forms & validation, §0.6 API inventory,
  §0.7 priority list.
- **Task tracker (in the assistant session):** #30 auth hardening, #31 client P0 (in progress),
  #32 staff P0, #33 docs+tests, #34 Flutter.
- **Where to add things:** controllers → `app/Http/Controllers/Api/V1/`; resources →
  `app/Http/Resources/`; shared logic → `app/Services/`; routes → `routes/api.php` (inside the
  `auth:sanctum` group); tests → `tests/Feature/Api/`.
