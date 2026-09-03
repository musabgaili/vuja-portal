# VujaDe API + Flutter App — Backlog & Sprint Plan

> **Scope:** Internal team app first (Manager, PM, Employee). Architecture is built to extend to Clients without structural changes.
> **Stack:** Laravel 12 + Sanctum + Socialite (API) · Flutter (mobile) · FCM (push) · Deep Links
> **API versioning:** `/api/v1/...`
> **Repo:** `musabgaili/vuja-portal-api` (mirror of `vuja-portal`, API-only branch of development)

---

## Repo Setup Checklist (Do Once)

> You need to do steps 1-2 manually, then the agent handles the rest.

1. **Create** `musabgaili/vuja-portal-api` on GitHub (private, empty — no README).
2. **Share** the repo URL so the agent can mirror the codebase and push it.
3. Agent will:
   - Mirror all code, history, and branches
   - Add `api` remote pointing to the new repo
   - Add FCM migration + Google Socialite config
   - Scaffold missing API controllers

---

## High-Level Architecture

```
Flutter App
    │
    │  HTTPS + Bearer Token (Sanctum)
    ▼
Laravel API (/api/v1)
    ├── Auth         — Sanctum tokens + Google OAuth (Socialite) + FCM device registration
    ├── Core         — Users, Roles, Permissions, Profile
    ├── Projects     — CRUD, milestones, tasks, deliverables, scope changes
    ├── Chat         — Channels, DMs, messages, threads, reactions, attachments
    ├── Meetings     — List, show, confirm, invite
    ├── Approvals    — Unified approval queue
    ├── Engagement   — Impact points, targets, weekly planner
    ├── Financials   — Quotes, invoices, payment requests, spend requests
    ├── Notifications — Bell feed + FCM push
    └── [Future] Clients — Service requests, quotes, NDA sign, deliverable confirm
```

---

## Roles in Scope (Phase 1)

| Role | Code value | Can do |
|---|---|---|
| Manager | `manager` | Full access, approvals, team management |
| Project Manager | `project_manager` | Projects, tasks, milestones, financials |
| Employee | `employee` | Tasks, time, chat, weekly plan, engagement |
| _(Future)_ Client | `client` | Service requests, projects (read), chat (limited) |

---

## Backlog

### EPIC 1 — Foundation & Auth

| ID | Story | Notes |
|---|---|---|
| A-1 | Email + password login → Sanctum token | Already exists, needs polish |
| A-2 | Google OAuth login (Socialite) → token | `provider` + `provider_id` columns exist on User |
| A-3 | Apple Sign-In (Socialite or manual JWT) | Required for App Store |
| A-4 | Register new internal user (manager-only) | No self-register for internal staff |
| A-5 | Logout (revoke current device token) | Done |
| A-6 | Logout all devices (revoke all tokens) | New |
| A-7 | `GET /me` — profile payload with role flags | Done, needs FCM token field |
| A-8 | `PATCH /me` — update name, avatar, language | New |
| A-9 | `POST /me/fcm-token` — register/refresh FCM device token | New |
| A-10 | `DELETE /me/fcm-token` — unregister on logout | New |
| A-11 | Password change endpoint | New |
| A-12 | Deep link callback handler for Google OAuth (`/auth/google/callback`) | Redirect to app scheme |

---

### EPIC 2 — Dashboard & Home Feed

| ID | Story | Notes |
|---|---|---|
| D-1 | `GET /dashboard` — role-aware summary (open tasks, pending approvals, unread chat, upcoming meetings) | New |
| D-2 | `GET /activity-feed` — paginated recent activity log | Spatie activitylog exists |
| D-3 | `GET /stats` — manager: team KPIs; PM: project health; employee: own targets | New |

---

### EPIC 3 — Notifications

| ID | Story | Notes |
|---|---|---|
| N-1 | `GET /notifications` — paginated bell feed | Done |
| N-2 | `POST /notifications/seen` — mark all seen | Done |
| N-3 | `PATCH /notifications/{id}/read` — mark single read | New |
| N-4 | FCM push on key events (task assigned, message mention, approval needed, meeting reminder) | New — needs `FcmNotification` channel |
| N-5 | `PUT /notification-settings` — per-user preferences | New (web version exists) |

---

### EPIC 4 — Projects

| ID | Story | Notes |
|---|---|---|
| P-1 | `GET /projects` — list with filters (status, assigned, client) + pagination | New API endpoint |
| P-2 | `GET /projects/{id}` — full project detail (milestones, tasks summary, team) | New |
| P-3 | `POST /projects` — propose new project (any internal) | New |
| P-4 | `PATCH /projects/{id}` — update status, dates, metadata (PM/Manager) | New |
| P-5 | `POST /projects/{id}/close` | New |
| P-6 | `GET /projects/{id}/milestones` | New |
| P-7 | `POST /projects/{id}/milestones` | New |
| P-8 | `PATCH /milestones/{id}` — update, mark complete | New |
| P-9 | `GET /projects/{id}/tasks` — kanban data | New |
| P-10 | `POST /projects/{id}/tasks` | New |
| P-11 | `PATCH /tasks/{id}` — status, assignee, due date | New |
| P-12 | `DELETE /tasks/{id}` | New |
| P-13 | `GET /projects/{id}/deliverables` | New |
| P-14 | `POST /projects/{id}/deliverables` — upload file (Manager/PM) | New |
| P-15 | `POST /deliverables/{id}/confirm` — client confirms receipt | Future/client |
| P-16 | `GET /projects/{id}/comments` + `POST` | New |
| P-17 | `GET /projects/{id}/documents` + `POST` upload | New |
| P-18 | `GET /projects/{id}/scope-changes` | New |
| P-19 | `POST /projects/{id}/scope-changes` — submit scope change | New |
| P-20 | `POST /scope-changes/{id}/approve` / `reject` | New |
| P-21 | `GET /projects/{id}/expenses` + `POST` (PM) | New |
| P-22 | `GET /projects/{id}/complaints` + `POST` (any member) | New |

---

### EPIC 5 — Tasks (My Work Inbox)

| ID | Story | Notes |
|---|---|---|
| T-1 | `GET /my-tasks` — employee's assigned tasks across all projects, filterable by status | New |
| T-2 | `PATCH /tasks/{id}/status` — employee updates task status | New |
| T-3 | `GET /staff-tasks` — direct staff tasks (not project-linked) | New (web exists) |
| T-4 | `PATCH /staff-tasks/{id}/status` | Done (web), needs API port |

---

### EPIC 6 — Chat

| ID | Story | Notes |
|---|---|---|
| C-1 | `GET /chat/channels` — user's channel list with unread counts | New API port |
| C-2 | `GET /chat/channels/{id}/messages` — paginated, cursor-based | New |
| C-3 | `POST /chat/channels/{id}/messages` — send (text + attachments) | New |
| C-4 | `PATCH /chat/messages/{id}` — edit | New |
| C-5 | `DELETE /chat/messages/{id}` | New |
| C-6 | `POST /chat/messages/{id}/react` — emoji reaction | New |
| C-7 | `GET /chat/channels/{id}/thread/{messageId}` — thread messages | New |
| C-8 | `POST /chat/channels` — create channel | New |
| C-9 | `POST /chat/dm` — start DM | New |
| C-10 | `POST /chat/channels/{id}/members` — add members | New |
| C-11 | `GET /chat/mentions` — unread @mentions | New |
| C-12 | FCM push on new message (unread channel) + @mention | Depends N-4 |
| C-13 | File upload endpoint for chat attachments | New |
| C-14 | `GET /chat/browse` — discover public channels | New |
| C-15 | `POST /chat/channels/{id}/join` — join request | New |

---

### EPIC 7 — Meetings

| ID | Story | Notes |
|---|---|---|
| M-1 | `GET /meetings` — upcoming + past, role-filtered | Done (polish) |
| M-2 | `GET /meetings/{id}` — detail + attendees | Done (polish) |
| M-3 | `POST /meetings` — create meeting with attendees | New |
| M-4 | `PATCH /meetings/{id}` — reschedule, update | New |
| M-5 | `POST /meetings/{id}/confirm` — attendee confirms | New |
| M-6 | FCM push 30 min before meeting | Depends N-4 |

---

### EPIC 8 — Approvals

| ID | Story | Notes |
|---|---|---|
| AP-1 | `GET /approvals` — unified queue (quotes, scope changes, spend, proposals, milestone approvals) | New |
| AP-2 | `POST /approvals/{type}/{id}/approve` | New |
| AP-3 | `POST /approvals/{type}/{id}/reject` | New |
| AP-4 | FCM push when item enters Manager's queue | Depends N-4 |

---

### EPIC 9 — Engagement & Performance

| ID | Story | Notes |
|---|---|---|
| E-1 | `GET /engagement` — my impact points, rank, recent activity | Done (polish) |
| E-2 | `POST /engagement/thank-you` — send token to colleague | New API port |
| E-3 | `GET /my-targets` — performance targets + progress | New |
| E-4 | `GET /weekly-plan` — current week's plan | New |
| E-5 | `POST /weekly-plan` — submit/update plan | New |
| E-6 | `GET /capacity` — my weekly allocation | New |
| E-7 | `PUT /capacity/hours` | New |

---

### EPIC 10 — Financials

| ID | Story | Notes |
|---|---|---|
| F-1 | `GET /quotes` — list quotes by project or client | New |
| F-2 | `GET /quotes/{id}` — detail with items, milestones, scope | New |
| F-3 | `POST /quotes/{id}/approve` / `reject` | New |
| F-4 | `GET /invoices` — list | New |
| F-5 | `GET /invoices/{id}` | New |
| F-6 | `GET /payment-requests` — list | New |
| F-7 | `GET /payment-requests/{id}` | New |
| F-8 | `GET /spend-requests` — team spend queue | New |
| F-9 | `POST /spend-requests` — submit expense | New |
| F-10 | `POST /spend-requests/{id}/approve` / `reject` (Manager) | New |

---

### EPIC 11 — Team & CRM (Manager/PM)

| ID | Story | Notes |
|---|---|---|
| TM-1 | `GET /team` — member list with roles, capacity, active projects | New |
| TM-2 | `GET /team/{id}` — member profile + targets + recent activity | New |
| TM-3 | `GET /crm/contacts` — contact list | New |
| TM-4 | `GET /crm/opportunities` — pipeline | New |
| TM-5 | `GET /crm/activities` — activity log | New |

---

### EPIC 12 — Deep Links & App Infrastructure

| ID | Story | Notes |
|---|---|---|
| DL-1 | Universal link / App link config (`apple-app-site-association`, `assetlinks.json`) served from Laravel | New |
| DL-2 | Deep link routes: `/app/projects/{id}`, `/app/chat/{channelId}`, `/app/tasks/{id}` | New |
| DL-3 | OAuth redirect back to Flutter app scheme (`vujade://auth/callback`) | Needed for A-2, A-3 |
| DL-4 | FCM notification payload includes `deep_link` field so tapping opens correct screen | Depends N-4 |

---

### EPIC 13 — [Future] Client Features

| ID | Story | Notes |
|---|---|---|
| CL-1 | Client self-registration + OTP | Not in Phase 1 |
| CL-2 | Submit service requests | Not in Phase 1 |
| CL-3 | View project status + milestones | Not in Phase 1 |
| CL-4 | Confirm deliverables | Not in Phase 1 |
| CL-5 | Download documents | Not in Phase 1 |
| CL-6 | Sign scope changes | Not in Phase 1 |
| CL-7 | Chat with team (limited channel) | Not in Phase 1 |
| CL-8 | Engagement/loyalty points | Not in Phase 1 |

---

## Sprint Plan (6 Sprints × 2 Weeks)

> Each sprint targets a shippable API slice + corresponding Flutter screens.

---

### Sprint 1 — Auth, Deep Links, FCM Foundation

**Goal:** The app can log in (email + Google), register a device for FCM, and open deep links.

**API tasks:**
- A-1 polish (device_name, token expiry policy)
- A-2 Google OAuth via Socialite (`GET /auth/google/redirect`, `GET /auth/google/callback`) → returns token to app via deep link
- A-3 Apple Sign-In skeleton
- A-5, A-6, A-7, A-8, A-9, A-10, A-11
- DL-1, DL-2, DL-3
- Migration: add `fcm_tokens` table (user_id, token, device, platform, updated_at)

**Flutter tasks:**
- Project scaffold (flavor: dev/prod, routing via `go_router`)
- Splash + Login screen (email/password)
- Google Sign-In button + OAuth deep link callback
- FCM initialization + token registration call
- `AuthBloc` / `AuthNotifier` with token persistence
- Deep link router stub (captures links, routes to placeholder screens)

**Definition of Done:** A team member can log in via Google on a real device, FCM token is stored, and tapping a `vujade://app/projects/1` link opens the app.

---

### Sprint 2 — Home Dashboard, Notifications, My Tasks

**Goal:** After login the user sees a useful home screen and gets push notifications.

**API tasks:**
- D-1, D-2, D-3
- N-1, N-2, N-3, N-4, N-5
- T-1, T-2, T-3, T-4
- FCM channel setup (Laravel `FcmNotification` channel using `google/apiclient` or `kreait/firebase-php`)

**Flutter tasks:**
- Home screen with role-aware summary cards (open tasks, pending approvals, unread messages, next meeting)
- Notification bell drawer (pull-to-refresh, mark read)
- FCM foreground + background handler → local notification display
- My Tasks screen (list + status chip, swipe to update status)
- Bottom nav: Home | Chat | Projects | Tasks | More

**Definition of Done:** Employee receives a push notification when assigned a task; tapping it navigates to the task detail screen.

---

### Sprint 3 — Projects (List, Detail, Kanban, Milestones)

**Goal:** Full project visibility and PM-level updates from the app.

**API tasks:**
- P-1 through P-12 (projects, milestones, tasks CRUD)
- P-16 (comments)

**Flutter tasks:**
- Projects list screen (filter by status, search)
- Project detail screen (tabs: Overview · Milestones · Tasks · Team)
- Milestone list + progress bar + complete action
- Kanban board for tasks (draggable cards via `flutter_kanban` or custom)
- Task detail bottom sheet (assignee, due date, status, comments)
- Task create/edit form
- Project detail deep link (`/app/projects/{id}`)

**Definition of Done:** A PM can open a project, move a task from "In Progress" to "Done", and add a milestone — all reflected on the web portal instantly.

---

### Sprint 4 — Chat (Channels, DMs, Mentions, Attachments)

**Goal:** Full team chat available in the app with push on mentions.

**API tasks:**
- C-1 through C-15
- C-12 FCM push on mention / new message in unread channel
- C-13 file upload (Spatie Media Library, presigned or direct)

**Flutter tasks:**
- Channel list screen with unread badges
- Chat conversation screen (bubble UI, paginated, pull-up for older)
- Message actions: edit, delete, react, reply-in-thread
- Thread view screen
- DM list + start DM flow
- Attachment picker (image, file) + upload progress
- @mention autocomplete in composer
- Create channel bottom sheet
- Browse & join channels
- Chat deep link (`/app/chat/{channelId}`)

**Definition of Done:** Two team members can hold a real conversation including file sharing; mentions trigger a push notification with a deep link.

---

### Sprint 5 — Meetings, Approvals, Financials

**Goal:** Managers can process the approval queue and financials; everyone can see meetings.

**API tasks:**
- M-1 through M-6
- AP-1 through AP-4
- F-1 through F-10

**Flutter tasks:**
- Meetings screen (upcoming calendar strip + list; past tab)
- Meeting detail (attendees, agenda, confirm action)
- Create meeting form
- Approvals screen (unified queue, role-filtered, swipe approve/reject)
- Quotes list + detail (read-only for employees)
- Invoices list + detail
- Spend request submit form
- Spend request queue (manager view, approve/reject)
- Payment requests read-only list

**Definition of Done:** A manager receives a push when a spend request is submitted and can approve it from the app; employee gets FCM confirmation.

---

### Sprint 6 — Engagement, Team/CRM, Polish & Release Prep

**Goal:** Full engagement loop + team overview; app is release-ready for internal beta.

**API tasks:**
- E-1 through E-7
- TM-1 through TM-5
- DL-4 (FCM payloads include deep links)
- Remaining API polish, rate limit tuning, error response standardization

**Flutter tasks:**
- My Engagement screen (points, rank, activity feed, thank-you button)
- Performance targets screen (gauges, 6-month trend)
- Weekly planner screen (day-by-day hours input)
- Team directory (avatar grid, tap → member profile)
- Member profile screen (role, targets, active projects, contact)
- CRM contacts + opportunities list (read-only, manager/PM only)
- App settings screen (profile edit, notification prefs, change password, logout all)
- Error states, loading skeletons, empty states throughout
- Accessibility pass (semantic labels, contrast)
- Internal beta build (TestFlight + Firebase App Distribution)

**Definition of Done:** A complete internal team can use the app for daily work. TestFlight/FAD build distributed to testers.

---

## API Design Conventions

```
Base URL:     https://api.vujadesa.com/api/v1
Auth:         Authorization: Bearer {token}
Content-Type: application/json
Accept:       application/json

Success:      { "data": {...}, "meta": {...} }
Paginated:    { "data": [...], "meta": { "current_page", "last_page", "per_page", "total" } }
Error:        { "message": "...", "errors": { "field": ["..."] } }
```

All list endpoints support:
- `?page=` + `?per_page=` (max 100)
- `?sort=field` + `?direction=asc|desc`
- Resource-specific filters documented per endpoint

---

## Flutter App Architecture

```
lib/
├── core/
│   ├── api/          — Dio client, interceptors, token refresh
│   ├── auth/         — AuthNotifier, token storage (flutter_secure_storage)
│   ├── fcm/          — FCM init, foreground handler, deep link router
│   └── theme/        — colors, typography, spacing
├── features/
│   ├── auth/         — login, google_oauth, apple_signin
│   ├── dashboard/    — home feed, stats
│   ├── projects/     — list, detail, kanban, milestones, tasks
│   ├── chat/         — channels, conversation, thread, dm
│   ├── meetings/     — list, detail, create
│   ├── approvals/    — queue, actions
│   ├── engagement/   — points, targets, weekly plan
│   ├── financials/   — quotes, invoices, spend, payments
│   ├── team/         — directory, profile, crm
│   └── notifications/— bell feed, settings
├── shared/
│   ├── widgets/      — buttons, cards, inputs, avatar, badge
│   ├── models/       — generated from API (freezed + json_serializable)
│   └── utils/        — date, file, deep link helpers
└── main.dart
```

**State management:** Riverpod (AsyncNotifierProvider per feature)
**Routing:** `go_router` with deep link support
**HTTP:** `dio` + `retrofit` (type-safe API client generation)
**Code gen:** `freezed` + `json_serializable` for models
**Push:** `firebase_messaging`
**Auth:** `google_sign_in` + `sign_in_with_apple`
**Storage:** `flutter_secure_storage` for token, `shared_preferences` for prefs

---

## Next Steps (What Agent Does When You Share the New Repo URL)

1. Mirror full codebase + history to `vuja-portal-api`
2. Add `fcm_tokens` migration
3. Add Socialite Google config + routes (`/auth/google/redirect`, `/auth/google/callback`)
4. Add `assetlinks.json` + `apple-app-site-association` deep link files served from `public/`
5. Scaffold all missing API controllers (stubs with proper structure)
6. Add `FcmNotification` channel class
7. Standardize API error responses via a base `ApiController`
8. Create Flutter project scaffold in a sibling repo

---

## Open Decisions (Need Your Input)

| # | Question | Default if not answered |
|---|---|---|
| 1 | Apple Sign-In required for Phase 1? (App Store mandates it if Google login is offered) | Include in Sprint 1 |
| 2 | Real-time chat via WebSockets (Laravel Reverb/Pusher) or polling? | Polling in Sprint 4, WebSocket upgrade later |
| 3 | Flutter app in same repo (monorepo `/mobile`) or separate repo? | Separate repo `vuja-mobile` |
| 4 | API base domain — `api.vujadesa.com` or same domain `/api`? | Same domain |
| 5 | File storage — S3 or local for MVP? | Local (Spatie MediaLibrary, easy to swap) |
| 6 | Should the deep link domain match the web portal domain? | Yes — same domain, `/.well-known/` routes |
