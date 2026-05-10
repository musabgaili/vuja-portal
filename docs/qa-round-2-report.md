# VujaDe Portal - QA Round 2 Hardening Report

This document tracks how the round 1 issues from `portal_test_report.pdf` and `issues_and_improvments_log.pdf` were addressed in the `hardening/qa-round-1` work.

## Summary of fixes

| Round 1 ID | Severity | Area | Status | Notes |
| --- | --- | --- | --- | --- |
| C08 | Medium | UX feedback after actions | Fixed | Shared Bootstrap toast component (`resources/views/components/toast.blade.php`) included in the three main layouts. All controllers already flash `success` / `error`; toasts now render automatically. |
| S01 | Critical | IDOR on `/projects/show/{project}` and sub-resources | Fixed | New policies in `app/Policies/*` cover Project, Idea/Consultation/Research/IP/Copyright requests, ProjectDocument, ProjectDeliverable, ProjectMilestone, ProjectScopeChange, Meeting. Controllers replaced inline `if ($x->user_id !== $user->id)` checks with `$this->authorize(...)`. Routable models switched to UUID route keys via the `HasUuidRouteKey` trait + two new migrations. |
| S06 | Medium | `/logout` returning 404 on direct GET | Fixed | Added a safety `GET /logout` route in `routes/web.php` that logs the user out and redirects to `login`. Existing logout buttons still POST. |
| S09 | Medium | Generic 500 page leaking technical details | Fixed | Added branded `403/404/500/503` Blade views with EN + AR translations under `lang/{en,ar}/errors.php`. `bootstrap/app.php` now renders the friendly 500 view in non-local environments. |
| X02 | Critical | Password reset email never sent | Fixed | Mail driver switched to Resend (`resend/resend-laravel`), every existing `app/Mail/*` class now implements `ShouldQueue`, queue worker is part of `composer dev` and documented for production. |
| Issue 1.2 | High | Employees reaching `/internal/pricing-admin` | Fixed | New `IsManager` middleware (`is_manager`) gated all manager-only routes in `routes/internal.php` (pricing admin, team, permissions, stepper, queue review) and the manager-only project routes in `routes/projects.php`. |
| Issue 1.3 | High | Clients reaching `/internal` after re-auth | Fixed | Top-level `/internal` group now requires `is_internal`. Login/registration redirects use a dynamic `redirectTo()` based on `User::isInternal()`, so clients land on `client.dashboard` and internal users land on `internal.dashboard`. |
| Required Improvement (UX low) | Low | Pricing tool needs feedback during calculation | Fixed | Added a spinner section in `resources/views/pricing/tool.blade.php` that shows "Calculating..." while the cart re-renders. |
| Cleanup | - | Debug routes leaking role / dummy responses | Fixed | Removed `/test`, `/test12345678`, `/xx`, and the dead `Route::prefix('employees')` block. |
| Notification gap | - | Quote / scope / meeting flows completed silently | Fixed | New queued mailables: `QuoteApproved`, `QuoteRejected`, `ScopeChangeRequested`, `ScopeChangeDecision`, `MeetingBooked`, `MeetingConfirmed`. Wired into `IdeaRequestController`, `ScopeChangeController`, `MeetingController`. |

## Manual regression checklist

The following scenarios from `portal_test_report.pdf` should be re-run against the hardened branch. Update the table when each case is reverified end to end.

### Client flows (C01 - C09)

- [ ] C01 - C07: existing happy paths continue to work after policy + UUID changes.
- [ ] C08: success toast appears after approving a quote / submitting a request / approving a milestone.
- [ ] C09: redirects from auth land on `/client/dashboard`.

### Project Manager flows (PM01 - PM10)

- [ ] PM01 - PM10: project create/update/delete now restricted to managers, regular PM can still manage milestones, tasks, expenses, scope changes.

### Employee flows (E01 - E08)

- [ ] E01 - E08: employees can still see assigned items but cannot reach `/internal/pricing-admin`, `/internal/team`, `/internal/permissions`, or `/internal/projects/create`.

### Manager flows (M01 - M05)

- [ ] M01 - M05: manager-only screens still accessible, including the new scope-change queue.

### Security regression (S01 - S10)

- [ ] S01: tampering with another client's UUID returns 403.
- [ ] S02 - S05, S07, S08, S10: existing safeguards still pass.
- [ ] S06: GET `/logout` redirects to login without 404.
- [ ] S09: forced 500 shows the branded error page in production builds.

### Edge cases (X01 - X05)

- [ ] X01: validation errors continue to show inline.
- [ ] X02: password reset email arrives via Resend.
- [ ] X03 - X05: existing edge case behavior still passes.

## Operational follow-ups

- Verify `vujadesa.com` SPF / DKIM / DMARC in the Resend dashboard before flipping production.
- Configure a queue worker (Supervisor, systemd, or platform equivalent) running `php artisan queue:work --tries=3 --backoff=30` against the database queue.
- Run `php artisan migrate` after pulling so the two UUID migrations execute and existing rows are backfilled before the new policies start enforcing UUID lookups.
