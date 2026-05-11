# VujaDe Portal - QA Round 3 Follow-up

This document tracks the follow-up items raised in `new_report.pdf`.

## Status changes

| Area | Old status from report | New status after this pass | Evidence |
| --- | --- | --- | --- |
| Internal document upload UX | Partially fixed. Uploading from the manager workflow still felt unfinished inside the embedded documents screen. | Fixed in code | `app/Http/Controllers/Projects/DocumentController.php` now returns JSON success payloads for embedded requests, and `resources/views/projects/documents/index.blade.php` now submits upload, edit, and delete actions with `fetch` + `FormData`, shows visible feedback, closes the modal, and refreshes the embedded list. |
| Pricing tool quantity handling | Failed. Negative and zero quantities could still appear in the UI before the value was clamped. | Fixed in code | `resources/views/pricing/tool.blade.php` now normalizes quantities on `input`, `change`, and `blur`, keeps the cart state at a minimum of `1`, and updates the rendered totals from normalized values only. |
| Toast coverage on manager / JS-driven flows | Partially fixed. Some success and failure paths still used ad-hoc feedback. | Fixed in code | `resources/views/layouts/{app,dashboard,internal-dashboard}.blade.php` now expose a shared `showAppToast(...)` helper. Manager milestone and kanban flows use the shared helper, and the embedded documents page mirrors the same toast behavior locally and to the parent page when available. |
| Technical error exposure / friendly error handling | Partially fixed. Some exception paths still exposed raw technical messages. | Fixed in code | `app/Http/Controllers/MeetingController.php` and `app/Http/Controllers/Services/ConsultationRequestController.php` now log exception details and return user-safe feedback. `bootstrap/app.php` now renders the branded `403`, `404`, `500`, and `503` pages in non-local environments. |

## Local verification completed

- `php -l` passed for the changed PHP controllers and `bootstrap/app.php`.
- `php artisan view:cache` completed successfully, confirming the updated Blade views compile.

## Manual browser checks still recommended

- Upload a document from the real manager project workflow and confirm the modal closes, the iframe refreshes, and feedback is visible.
- Type `-1`, `0`, and pasted negative values into the pricing tool quantity field and confirm the value immediately returns to `1`.
- Re-check manager AJAX actions such as milestone completion and kanban status changes to confirm the shared toast appears as expected.
- Re-check a handled meeting / consultation scheduling failure path and a friendly `403` / `404` page in a non-local environment.
