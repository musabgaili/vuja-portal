# Vuja Portal Mobile — Build Plan & Roadmap

> **Audience: an AI coding agent (e.g. Cursor) continuing this work with no prior chat context.**
> Read `docs/mobile/KNOWLEDGE.md` first (state, architecture, conventions, git reality), then use
> `docs/mobile/AUDIT.md` for deep reference (functional inventory, data model, forms/validation,
> roles). This file is *what to do next and how*.
>
> **Branch:** do all API work on **`mobile-api`**. `main` has newer payments/invoices work not yet
> on this branch (see KNOWLEDGE §6). **Last updated:** 2026-07-04.

---

## 0. The per-item workflow (do this for EVERY item — non-negotiable)

```
1. git checkout mobile-api && git pull
2. php artisan test        # confirm baseline = 2 pre-existing AuthTest failures ONLY
3. READ the web source     # routes/*.php, the web controller, its FormRequest(s), the model(s).
                           #   Copy exact validation rules, status enums, field names. Never invent.
4. If the web logic is inline in a controller and the API needs it:
     extract it into app/Services/... (behaviour-preserving) AND add a web smoke test
     (tests/Feature/*WebTest.php) proving the web page still renders/works.
5. Add: app/Http/Controllers/Api/V1/<X>Controller.php  (+ app/Http/Resources/<X>Resource.php)
        route inside the auth:sanctum group in routes/api.php
6. Add tests/Feature/Api/<X>ApiTest.php  (guest 401, happy path, authz 403, validation 422)
7. php artisan test        # MUST show no NEW failures beyond the baseline 2
8. git add … && git commit && git push origin mobile-api
9. Update KNOWLEDGE.md §5 (endpoint table) and this file's checklist.
```

**Conventions to obey** (full detail in KNOWLEDGE §4): Sanctum bearer; `/api/v1`; flat JSON
(`withoutWrapping`); JSON errors for `api/*`; mirror the web's exact validation; reuse
FormRequests/Services; role gates via `user.isClient()/isInternal()/isManager()` or the
`is_internal`/`is_manager` middleware; localise with `__()`.

**Testing notes:** `Laravel\Sanctum\Sanctum::actingAs($user)`; `RefreshDatabase`; only `UserFactory`
exists (verified client by default; `['role'=>'employee']` makes an internal user). PHP-8.5 shows
passing tests as "DEPR" — that's fine; only "FAIL" matters.

---

## 1. Phase 1 — Client P0 API

**Done:** Auth, Profile (CRUD), Client Dashboard, Notifications, Engagement snapshot, Meetings (read),
**My Requests** unified feed. (Contracts in KNOWLEDGE §5.)

**Remaining, in order:**

### [ ] #4 — Create a service request (intake)
- **Web source:** `routes/client.php` (the `ideas`/`consultations`/`research`/`prototypes`/`threed`/
  `ip`/`copyright` prefixed groups) + each `App\Http\Controllers\Services\*Controller@store` + their
  FormRequests + models. **Read each `store()` and mirror its exact validation + file handling.**
- **Endpoints (per line, client-only):**
  `POST /api/v1/requests/idea`, `/consultation`, `/research`, `/threed`, `/ip`, `/copyright`
  (prototypes: `POST /api/v1/requests/prototype` — has its own intake). Return the created record as a
  resource + 201.
- **Reuse:** the existing FormRequest classes (validate via them). If a store() has inline validation,
  mirror the rules exactly. File uploads: accept `multipart/form-data`; reuse the same disk/paths as web.
- **Build factories** for the models you touch (none exist yet) so you can test happy-path creation.
- **Tests:** guest 401; client creates each type (201 + persisted); validation 422 with field errors;
  staff 403.
- **Done when:** each line creates a record identical to the web form, suite green.

### [ ] #5 — Projects (client)
- **Web source:** `projects.client.*` routes + controller(s) + models (`Project`, milestones, docs,
  comments, feedback).
- **Endpoints:** `GET /api/v1/projects` (list, scoped to caller), `GET /api/v1/projects/{project}`
  (detail: milestones, deliverables/docs, comments), `POST /api/v1/projects/{project}/milestones/{m}/approve`,
  `POST /api/v1/projects/{project}/comments`, `POST /api/v1/projects/{project}/feedback`. Downloads:
  return a signed URL or stream the file.
- **Resource:** `ProjectResource` (+ milestone/comment sub-shapes). Authz: caller must own/participate.
- **Done when:** list+detail match web, approve/comment/feedback work, suite green.

### [ ] #6 — Quotes (client)
- **Web source:** `quotes.client.*` (list/show/accept/reject/download). Note the Scope-Planner `Quote`
  model is rich; expose the **client-visible** fields only.
- **Endpoints:** `GET /api/v1/quotes`, `GET /api/v1/quotes/{quote}`,
  `POST /api/v1/quotes/{quote}/accept`, `POST /api/v1/quotes/{quote}/reject`,
  `GET /api/v1/quotes/{quote}/download` (PDF stream/URL). Mirror the web's accept/reject side-effects
  (signature/timestamps/status transition, project conversion) — **reuse the existing service/controller
  logic; extract if inline.**
- **Done when:** accept/reject produce the same state as web, suite green.

### [ ] #7 — Invoices (client)  ⚠ coordinate with payments on `main`
- **Web source:** `invoices.client.*` + the **Moyassar payments** work that now lives on `main`
  (KNOWLEDGE §6). **Merge `main` into `mobile-api` first** (see §4) so this is built against the current
  payment/invoice code, not a stale version.
- **Endpoints:** `GET /api/v1/invoices`, `GET /api/v1/invoices/{invoice}`,
  `POST /api/v1/invoices/{invoice}/receipt` (upload), and a **payment** endpoint/flow consistent with
  Moyassar (likely return a hosted-payment URL the app opens in a webview/browser, then poll status).
- **Done when:** list/detail/receipt work; payment flow matches the web's Moyassar integration.

### [ ] #8 — Engagement Points (client) — full ledger
- **Web source:** `engagement.client.*` + the engagement-points engine (`config/engagement_points.php`,
  ledger/redemptions/tiers/referrals). Today the API only has the `engagement` snapshot.
- **Endpoints:** `GET /api/v1/engagement/ledger` (paginated history), `GET /api/v1/engagement/tiers`,
  `GET /api/v1/engagement/rewards` (catalog), `POST /api/v1/engagement/redeem`. Mirror the clamp/vesting
  rules exactly (reuse the service).
- **Done when:** balances/ledger/redeem match web, suite green.

### [ ] #9 — Meetings: book + cancel
- **Web source:** client `MeetingController@availableSlots / create / store / cancel` (`routes/client.php`).
  Read exists already (`GET /meetings`, `GET /meetings/{meeting}`).
- **Endpoints:** `GET /api/v1/meetings/slots` (available slots, filters), `POST /api/v1/meetings`
  (book a slot; mirror `store()` validation + conflict checks), `DELETE /api/v1/meetings/{meeting}`
  (cancel). **Extract booking logic into a service if it's inline in the web controller.**
- **Done when:** booking/cancel produce the same records + notifications as web, suite green.

---

## 2. Phase 1 — Staff P0 API (after the client set)

Role-gate everything to `is_internal`. `GET /api/v1/dashboard` should **branch on `user.type`**
(internal → staff payload) — additive, not a rename.

- [ ] **Internal dashboard** (staff home counters — extract a `StaffDashboardService` like the client one).
- [ ] **Approvals** (weekly-plan review, spend requests, meeting requests) — read + approve/reject.
- [ ] **My Targets / Capacity** — the targets chip data, monthly attainment, weekly planner (read; submit later).
- [ ] **Staff meetings** — confirm/complete/cancel + invitations accept/decline (**extract the web
      `MeetingController` action logic into a service** so web+API share it — these have engagement-point
      side-effects; do NOT duplicate).
- [ ] **Chat-lite** — channels list, messages (paginated), send. Reuse the chat service.

---

## 3. Phase 1 — Cross-cutting

- [ ] **Auth hardening:** mobile **password-reset** endpoints (request + reset); **device-registration**
      endpoint (stub — stores device token for future FCM; Firebase off). `login` already takes `device_name`.
- [ ] **API docs:** `docs/mobile/API.md` (or Scribe) — one table per endpoint with request/response
      examples. Keep it in sync as you add endpoints.
- [ ] **Test data:** factories per model as you need them; consider a per-role seeder for manual QA.

---

## 4. Branch reconciliation (do before #7, and before any merge to main)

`mobile-api` is behind `origin/main` by the payments/invoices commits (KNOWLEDGE §6).

```
git checkout mobile-api && git pull
git merge origin/main            # bring payments/invoices onto the API branch
# resolve any conflicts (expected minimal — payments vs API touch different files;
#   watch routes/api.php, bootstrap/app.php, config/*)
php artisan test                 # baseline must still be just the 2 AuthTest failures
git push origin mobile-api
```
When the whole API is ready and green, open a PR **`mobile-api` → `main`** (or fast-forward if clean).

---

## 5. Phase 2 — Flutter app (BLOCKED — SDK not installed)

Do **not** start until the Flutter SDK is installed and on PATH (`flutter --version` works).

1. Install Flutter SDK; run `flutter doctor`.
2. Scaffold at `C:\Users\munzir.alradi\Documents\vuja_portal_mobile`, bundle id `com.vujade.portal`,
   launcher name **"Vujà Dé"**.
3. **Architecture:** feature-first + **Riverpod** (code-gen), `go_router` with role-based shells
   (branch on `user.type` from `GET /me`), `dio` → `/api/v1` (dev base `http://10.0.2.2:8000/api`),
   `flutter_secure_storage` for the token (on 401 → log out), `freezed`+`json_serializable` models,
   `flutter_localizations`+`intl` (en/ar, RTL), Material 3.
4. **Approved deps only** (full list in `MOBILE_API_HANDOFF.md` §8): riverpod, go_router, dio, freezed,
   json_serializable, flutter_secure_storage, flutter_localizations, intl, google_fonts,
   cached_network_image, skeletonizer, connectivity_plus, very_good_analysis. Push = stubbed.
5. **Design tokens** (from `public/css/app.css :root`; full in `AUDIT.md` §0.3): primary `#0C7075`,
   dark `#095055`, bright `#0F969C`, slate `#294D61`, accent `#6DA5C0`; header gradient
   `135deg #0F969C→#0C7075→#294D61`; success `#16a34a` warn `#d97706` error `#dc2626` info `#0891b2`;
   fonts **Almarai** + **Tajawal**; Western numerals in both locales.
6. Build screens against the endpoints in KNOWLEDGE §5, in the same order they were built.

---

## 6. Definition of done (every backend item)

- [ ] Validation/behaviour **mirrors the web exactly** (verified by reading the source).
- [ ] Shared logic extracted (not duplicated); any web refactor guarded by a web test.
- [ ] `Api\V1` controller + Resource + route (inside `auth:sanctum`).
- [ ] Feature test: guest 401, happy path, authz 403, validation 422.
- [ ] `php artisan test` → **no new failures** beyond the 2 baseline `AuthTest` cases.
- [ ] Committed + pushed to `origin/mobile-api`; KNOWLEDGE §5 + this checklist updated.

---

## 7. Open questions / decisions

- **My Requests shape:** RESOLVED → single unified feed (`GET /api/v1/requests`). Prototypes stay a
  separate list (mirrors web).
- **Create request (#4):** per-line POST endpoints (recommended) vs one polymorphic endpoint — default
  to **per-line** (simpler validation, matches the distinct web forms). Confirm if unsure.
- **Payments (#7):** confirm the Moyassar flow shape for mobile (hosted URL + status poll vs native) by
  reading the payment code merged from `main`.
