# Vuja Portal Mobile — Knowledge & Current State

> **What this is.** A single, accurate snapshot of the mobile effort: the goal, how it's
> built, exactly what exists today, and the git reality. Companion to `PLAN.md` (what's next).
> Deep reference: `docs/mobile/AUDIT.md` (Phase 0 audit) and `docs/mobile/MOBILE_API_HANDOFF.md`
> (the original session handoff).
>
> **Last updated:** 2026-07-04 · **Branch of record:** `mobile-api` (pushed to `origin/mobile-api`).

---

## 1. Goal

Build a **native Flutter mobile app** (Android + iOS, *not* a webview) for the existing **Vuja
Portal** Laravel web app. **One role-switched app** serves **both** external *clients* and internal
*staff*, branching on `user.type`. Scope is **broad v1**. Push notifications are **stubbed** (no
Firebase yet).

This is **two pieces of work**:
1. **Backend mobile API** — additive JSON + token API on the Laravel app. *(In progress.)*
2. **Flutter app** — the phone client. *(Not started — see §7.)*

---

## 2. Repos, locations, environment

| Thing | Where |
|---|---|
| Laravel app (API lives here) | `C:\Users\munzir.alradi\Documents\Vuja Portal` → GitHub `github.com/musabgaili/vuja-portal` |
| Flutter app (planned, not created) | `C:\Users\munzir.alradi\Documents\vuja_portal_mobile` — **does not exist yet** |
| Stack | Laravel 12, PHP 8.5.4, Composer 2.9.5, **sqlite** dev DB, Sanctum 4.3, spatie permission+medialibrary, bilingual en/ar (RTL) |
| Serve web | `php artisan serve` → http://localhost:8000 · seeded login password **`12345678`** |
| Dev API base (Android emulator) | `http://10.0.2.2:8000/api` |
| Run tests | `php artisan test` (phpunit, sqlite `:memory:`, `RefreshDatabase`) |
| PHP 8.5 quirks | `composer` needs `--ignore-platform-req=php`; PHP-8.5 `PDO::MYSQL_ATTR_*` **deprecation** notices are cosmetic, not failures (PHPUnit buckets such tests as "DEPR", they still pass) |

**Test baseline (pre-existing, NOT ours):** `tests/Feature/AuthTest.php` has **2 failing tests**
(`user can register…`, `user can login…`) — they assert a redirect to `/home` but the app correctly
redirects to `/client/dashboard`. **Our guarantee: no *new* failures beyond these two.**

---

## 3. Hard constraints (the rules this work follows)

1. **Additive backend only.** New `Api\V1` controllers, resources, middleware, services, routes.
   **Zero breaking changes** to web routes/controllers/Blade. *Allowed exception:* behaviour-preserving
   **extraction** of controller logic into a shared Service both web+API call — only with a test that
   proves the web behaviour is unchanged. (Done twice: dashboard stats, My Requests.)
2. **Read before you write.** Every validation rule / status enum / field name comes from the actual
   codebase — never invented or approximated.
3. **Reuse, don't duplicate.** Reuse FormRequests/Services; extract inline controller logic rather
   than copy it.
4. **Native, not a webview.**
5. **Phase by phase, stop-and-verify.** Small batches; at each checkpoint run the suite, confirm no new
   failures vs baseline, commit, summarise.
6. **Firebase off → push stubbed.**

---

## 4. API architecture & conventions

- **Auth:** Laravel Sanctum **personal access tokens** (bearer). Token **TTL = 30 days**
  (`config/sanctum.php` → `expiration`). Client stores the token in secure storage; on **401 → log out**.
- **Routing:** everything under `routes/api.php`, prefix **`v1`**. Public: `login` (`throttle:10,1`).
  Everything else inside `Route::middleware('auth:sanctum')`.
- **JSON envelope:** `JsonResource::withoutWrapping()` (AppServiceProvider) → single resources are a
  **flat object** (no `data` wrapper). Paginated collections keep `data`+meta; ad-hoc payloads use
  `response()->json([...])`.
- **Errors always JSON for `api/*`** (`bootstrap/app.php` → `shouldRenderJsonWhen` + `respond` guard):
  validation → **422** `{message, errors}`; unauth → **401**; forbidden → **403**. Never an HTML page
  or login redirect.
- **Localisation:** `App\Http\Middleware\ApiLocalization` reads `Accept-Language` → sets locale per
  request (stateless). Supported `en`, `ar`.
- **Validation:** mirror the web's exact rules (inline if the web is inline). Use `current_password:sanctum`.
- **Resources:** shaping in `app/Http/Resources/*`. **Shared logic** the web+API both need goes in
  `app/Services/...` (so numbers can't drift).
- **Where things go:** controllers → `app/Http/Controllers/Api/V1/`; resources → `app/Http/Resources/`;
  shared logic → `app/Services/`; routes → `routes/api.php` (inside the `auth:sanctum` group);
  tests → `tests/Feature/Api/`.

**Roles model:** `user.type` (`internal` | `client`) = staff vs client surface; `user.role`
= `client | employee | manager | project_manager`. Real gates are middleware aliases `is_internal`,
`is_manager`, `can_manage_projects` (spatie roles are seeded but not route-enforced). `UserResource`
exposes `is_*` booleans so the app branches UI.

---

## 5. What exists today (the current API surface)

All under `/api/v1`, all `auth:sanctum` except `login`.

### Base API (on `main` and `mobile-api`)
| Method | Path | Contract |
|---|---|---|
| POST | `login` | `{email,password,device_name?}` → `{token, user}`. Bad creds → 422 generic. Suspended/inactive → 422. `throttle:10,1`. |
| GET | `me` | → `UserResource` (flat). |
| POST | `logout` | revokes **only the current** token. |
| GET | `notifications?limit=` | limit 1–50 → `{unread_count, items}` (NotificationService). |
| POST | `notifications/seen` | clears unread badge → `{ok:true}`. |
| GET | `engagement` | `{impact_points, level_index, progress}` + staff `targets_attainment` (int %). |
| GET | `meetings` | paginated `MeetingResource` (MeetingService). |
| GET | `meetings/{meeting}` | uuid key; 403 unless participant/attendee/manager/PM. |

### Added on `mobile-api` only
| Method | Path | Contract |
|---|---|---|
| GET | `dashboard` | `{stats{…9 counters…}, recent_activity[], active_projects[]}` (ClientDashboardService). |
| GET | `profile` | → `UserResource`. |
| PUT | `profile` | `{name(req), phone?(digits,max20)}`. |
| PUT | `profile/email` | `{email(unique), current_password}` → resets verification via **forceFill**. |
| PUT | `profile/password` | `{current_password, password, password_confirmation}` (`Password::defaults()`). |
| PUT | `profile/phone` | `{phone?}`. |
| DELETE | `profile` | `{password, confirmation:"DELETE"}` → revokes all tokens, deletes account. |
| GET | `requests?status=&type=&page=` | **My Requests** unified feed → `{items, summary, pagination}`. Client-only (403 staff). |

**Resources:** `UserResource` `{id,name,email,phone,role,type,status,is_internal,is_client,is_manager,is_project_manager,impact_points}`.
`MeetingResource` `{id(uuid),title,description,notes,status,status_label,scheduled_at,ends_at,duration_minutes,meeting_link,with,attendees?,can_confirm,can_complete}`.

**Shared services (web+API, each guarded by a web test):**
- `app/Services/Dashboard/ClientDashboardService` — client home counters (web `DashboardController` refactored to use it; guard `ClientDashboardWebTest`).
- `app/Services/Client/ClientRequestsService` — My Requests aggregation across **6** lines
  (idea/consultation/research/ip/copyright/threed; **prototypes have their own list**). Web
  `ClientRequestsController` is now a thin call; guard `ClientRequestsWebTest`.

**Tests:** `tests/Feature/Api/` (`DashboardApiTest`, `ProfileApiTest`, `RequestsApiTest`) +
web guards (`ClientDashboardWebTest`, `ClientRequestsWebTest`). Suite is green vs the 2-failure baseline.

---

## 6. Git reality (important)

Two branches have **diverged** off a shared base (`9967ad2` = the base API):

- **`origin/mobile-api`** (`5c385b0`, in sync) — the mobile API: audit, foundation (locale mw + 30-day
  TTL), Profile, Dashboard, **My Requests**, handoff/docs, tests. **Fully pushed.**
- **`origin/main`** (`3aec9c8`) — has the base API **plus 7 newer commits of other work**
  (Moyassar **payments/invoices**, guest invoices, an editor fix) pushed in parallel. `mobile-api`
  does **not** have these yet.

They share the base and haven't conflicted (different files: payments vs API). **Neither is merged
into the other.** Local `main` is stale (behind `origin/main`) — origin is the source of truth;
`git pull` before ever committing on `main`.

---

## 7. Flutter app — not started (blocked)

The **Flutter SDK is not installed / not on PATH** on this machine, so Phase 2 hasn't begun and
`vuja_portal_mobile` does not exist. App identity is decided: launcher **"Vujà Dé"**, bundle
`com.vujade.portal`. Approved deps + design tokens are in `MOBILE_API_HANDOFF.md` §8 and `AUDIT.md` §0.3.

---

## 8. Known issues / watch-outs

- **Web email-verify bug (unfixed, out of scope):** `ProfileController::updateEmail` (web) sets
  `email_verified_at => null` via `update()`, but that column isn't in User `$fillable`, so it's
  **silently dropped** — changing email on the website does *not* reset verification. The mobile API
  does it correctly with `forceFill`. Raise as its own small fix.
- **Branch divergence** (§6) — must reconcile `mobile-api` with the newer `main` before merging.
- **No factories** for the 6/7 request models — API list tests use the empty-state contract; build
  factories as you add write endpoints (Projects/Quotes/etc.).
- **Only `UserFactory`** exists (defaults to a verified client; flips `type→internal` when `role` is internal).
