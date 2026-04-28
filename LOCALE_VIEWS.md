# Locale coverage (Blade)

Track **en** / **ar** work for [`lang/en.json`](lang/en.json) and [`lang/ar.json`](lang/ar.json).  
**Skipped:** `resources/views/vendor/**` (published packages). **In scope:** everything else under `resources/views/`, including **emails**.

**Last batch:** Pricing — `portal.pricing.*`

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

- [ ] `consultations/create.blade.php`
- [ ] `consultations/show.blade.php`
- [ ] `consultations/manager/index.blade.php`
- [ ] `consultations/manager/show.blade.php`

## Copyright

- [ ] `copyright/create.blade.php`
- [ ] `copyright/show.blade.php`
- [ ] `copyright/manager/index.blade.php`
- [ ] `copyright/manager/show.blade.php`

## Emails

- [ ] `emails/complaint-alert.blade.php`
- [ ] `emails/complaint-resolved.blade.php`
- [ ] `emails/milestone-approved.blade.php`
- [ ] `emails/milestone-completed.blade.php`
- [ ] `emails/project-completed.blade.php`
- [ ] `emails/request-received.blade.php`
- [ ] `emails/request-responded.blade.php`
- [ ] `emails/team-invitation.blade.php`

## Home / misc

- [x] `home.blade.php`
- [x] `welcome.blade.php`

## Ideas

- [ ] `ideas/ai-assessment.blade.php`
- [ ] `ideas/create.blade.php`
- [ ] `ideas/manager/index.blade.php`
- [ ] `ideas/manager/show.blade.php`
- [ ] `ideas/negotiation.blade.php`
- [ ] `ideas/payment.blade.php`
- [ ] `ideas/show.blade.php`

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

- [ ] `profile/edit.blade.php`
- [ ] `profile/security.blade.php`
- [ ] `profile/show.blade.php`

## Projects — client

- [ ] `projects/client/feedback.blade.php`
- [ ] `projects/client/index.blade.php`
- [ ] `projects/client/index-new.blade.php`
- [ ] `projects/client/index-old.blade.php`
- [ ] `projects/client/scope-change.blade.php`
- [ ] `projects/client/show.blade.php`
- [ ] `projects/client/show-new.blade.php`
- [ ] `projects/client/show-old.blade.php`

## Projects — manager

- [ ] `projects/manager/create.blade.php`
- [ ] `projects/manager/expenses.blade.php`
- [ ] `projects/manager/index.blade.php`
- [ ] `projects/manager/kanban.blade.php`
- [ ] `projects/manager/scope-changes.blade.php`
- [ ] `projects/manager/show.blade.php`
- [ ] `projects/manager/show-modals.blade.php`
- [ ] `projects/manager/show-new.blade.php`
- [ ] `projects/manager/show-old.blade.php`

## Projects — shared

- [ ] `projects/documents/index.blade.php`

## Research

- [ ] `research/create.blade.php`
- [ ] `research/show.blade.php`
- [ ] `research/manager/index.blade.php`
- [ ] `research/manager/show.blade.php`

## Service requests

- [ ] `service-requests/create.blade.php`
- [ ] `service-requests/index.blade.php`
- [ ] `service-requests/review-queue.blade.php`
- [ ] `service-requests/show.blade.php`

## Services

- [ ] `services/index.blade.php`

## Stepper

- [ ] `stepper/client/index.blade.php`
- [ ] `stepper/service-types/create.blade.php`
- [ ] `stepper/service-types/index.blade.php`

## Team

- [x] `team/index.blade.php`
- [x] `team/invite.blade.php`

## Time slots

- [ ] `time-slots/create.blade.php`
- [ ] `time-slots/my-slots.blade.php`
- [ ] `time-slots/team-slots.blade.php`

---

**Excluded:** `resources/views/vendor/**` (4 files) — do not list; package updates may replace them.

**Total:** 93 Blade files in this checklist + 1 `N/A` partial line (not counted as a locale batch target).
