# Locale coverage (Blade)

Track **en** / **ar** work for [`lang/en.json`](lang/en.json) and [`lang/ar.json`](lang/ar.json).  
**Skipped:** `resources/views/vendor/**` (published packages). **In scope:** everything else under `resources/views/`, including **emails**.

**Last batch:** Ideas (client + manager) & time slots — `portal.ideas.*`, `portal.time_slots.*`

---

## Batch rules (keep sessions small)

| Rule | Why |
|------|-----|
| One batch = one focused patch | Small diff, easy review |
| Target **3–8** Blade files per batch (or one tiny subsection) | Fits agent context |
| Add JSON keys **only** for strings touched in that batch | Avoid giant JSON |
| Update **this file’s checkboxes** in the same patch | Checklist stays true |
| Never “localize all unchecked” | Always name files or a section in the prompt |

## Handoff prompt (copy per batch)

```text
Open LOCALE_VIEWS.md. Localize ONLY this batch: [list sections or paths under resources/views/].
Replace visible user copy with __('portal....') keys, add entries to lang/en.json and lang/ar.json (same keys), then mark those lines [x] in LOCALE_VIEWS.md. One cohesive patch; do not touch files outside this batch.
```

Optional: `Key prefix for this batch: portal.<area>.` (e.g. `portal.auth.`).

## Legend

- `[ ]` — not localized (or only partial; treat as todo until `[x]`).
- `[x]` — strings use `__()` / `@lang` and keys exist in **both** `lang/en.json` and `lang/ar.json`.
- `N/A` — no user-facing copy to translate (or keys only for locale UI).

## After many batches: JSON cleanup

Merge duplicate **keys** where the same phrase reused many times, rename keys consistently, then update Blade `__()` calls in a dedicated pass. See todo `json-dedupe-later` in project planning.

---

## Layouts

- [x] `layouts/app.blade.php`
- [x] `layouts/dashboard.blade.php` _(lang/dir, switcher; sidebar nav still English — future batch)_
- [x] `layouts/internal-dashboard.blade.php` _(same)_

## Partials

- N/A `partials/locale-switcher.blade.php` _(only `portal.language_*` labels)_

## Auth

- [x] `auth/login.blade.php`
- [x] `auth/register.blade.php`
- [x] `auth/verify.blade.php`
- [x] `auth/passwords/confirm.blade.php`
- [x] `auth/passwords/email.blade.php`
- [x] `auth/passwords/reset.blade.php`

## Client

- [x] `client/dashboard.blade.php`
- [x] `client/requests.blade.php`

## Consultations

- [x] `consultations/create.blade.php`
- [x] `consultations/show.blade.php`
- [x] `consultations/manager/index.blade.php`
- [x] `consultations/manager/show.blade.php`

## Copyright

- [x] `copyright/create.blade.php`
- [x] `copyright/show.blade.php`
- [x] `copyright/manager/index.blade.php`
- [x] `copyright/manager/show.blade.php`

## Emails

- [x] `emails/complaint-alert.blade.php`
- [x] `emails/complaint-resolved.blade.php`
- [x] `emails/milestone-approved.blade.php`
- [x] `emails/milestone-completed.blade.php`
- [x] `emails/project-completed.blade.php`
- [x] `emails/request-received.blade.php`
- [x] `emails/request-responded.blade.php`
- [x] `emails/team-invitation.blade.php`

## Home / misc

- [x] `home.blade.php`
- [x] `welcome.blade.php`

## Ideas

- [x] `ideas/ai-assessment.blade.php`
- [x] `ideas/create.blade.php`
- [x] `ideas/manager/index.blade.php`
- [x] `ideas/manager/show.blade.php`
- [x] `ideas/negotiation.blade.php`
- [x] `ideas/payment.blade.php`
- [x] `ideas/show.blade.php`

## Internal dashboards

- [x] `internal/dashboard.blade.php`
- [x] `internal/employee-dashboard.blade.php`
- [x] `internal/manager-dashboard.blade.php`

## IP

- [x] `ip/create.blade.php`
- [x] `ip/show.blade.php`
- [x] `ip/manager/index.blade.php`
- [x] `ip/manager/show.blade.php`

## Manager (legacy layout)

- [x] `manager/dashboard.blade.php`

## Meetings

- [x] `meetings/available-slots.blade.php`
- [x] `meetings/create.blade.php`
- [x] `meetings/my-meetings.blade.php`

## Permissions

- [x] `permissions/index.blade.php`
- [x] `permissions/permissions.blade.php`
- [x] `permissions/roles.blade.php`
- [x] `permissions/users.blade.php`

## Pricing

- [x] `pricing/admin.blade.php`
- [x] `pricing/quoting-tasks.blade.php`
- [x] `pricing/tool.blade.php`

## Profile

- [x] `profile/edit.blade.php`
- [x] `profile/security.blade.php`
- [x] `profile/show.blade.php`

## Projects — client

- [x] `projects/client/feedback.blade.php`
- [x] `projects/client/index.blade.php`
- [x] `projects/client/index-new.blade.php`
- [x] `projects/client/index-old.blade.php`
- [x] `projects/client/scope-change.blade.php`
- [x] `projects/client/show.blade.php`
- [x] `projects/client/show-new.blade.php`
- [x] `projects/client/show-old.blade.php`

## Projects — manager

- [x] `projects/manager/create.blade.php`
- [x] `projects/manager/expenses.blade.php`
- [x] `projects/manager/index.blade.php`
- [x] `projects/manager/kanban.blade.php`
- [x] `projects/manager/scope-changes.blade.php`
- [x] `projects/manager/show.blade.php`
- [x] `projects/manager/show-modals.blade.php`
- [x] `projects/manager/show-new.blade.php`
- [x] `projects/manager/show-old.blade.php`

## Projects — shared

- [x] `projects/documents/index.blade.php`

## Research

- [x] `research/create.blade.php`
- [x] `research/show.blade.php`
- [x] `research/manager/index.blade.php`
- [x] `research/manager/show.blade.php`

## Service requests

- [x] `service-requests/create.blade.php`
- [x] `service-requests/index.blade.php`
- [x] `service-requests/review-queue.blade.php`
- [x] `service-requests/show.blade.php`

## Services

- [x] `services/index.blade.php`

## Stepper

- [x] `stepper/client/index.blade.php`
- [x] `stepper/service-types/create.blade.php`
- [x] `stepper/service-types/index.blade.php`

## Team

- [x] `team/index.blade.php`
- [x] `team/invite.blade.php`

## Time slots

- [x] `time-slots/create.blade.php`
- [x] `time-slots/my-slots.blade.php`
- [x] `time-slots/team-slots.blade.php`

---

**Excluded:** `resources/views/vendor/**` (4 files) — do not list; package updates may replace them.

**Total:** 93 Blade files in this checklist + 1 `N/A` partial line (not counted as a locale batch target).
