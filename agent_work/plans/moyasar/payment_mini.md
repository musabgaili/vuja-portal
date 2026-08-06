# Moyasar Mini Payment Plan

## Scope

- [ ] Build a standalone `PaymentRequest` flow; do not connect invoices, quotes, projects, or service requests yet.
- [ ] Keep it reusable through nullable polymorphic `payable_type` / `payable_id` fields so later modules can create payment requests without duplicating payment logic.
- [ ] Restrict all administration actions to authenticated managers via the existing `auth + is_internal + is_manager` middleware.
- [ ] Support SAR and credit cards only (`methods: ['creditcard']`); no Apple Pay or STC Pay.

## User flow

- [ ] Manager opens one simple payment-management view containing the create form and recent payment requests/statuses.
- [ ] Manager enters `name`, `email`, `phone`, `title`, `description`, `quantity`, and unit `amount`; total is calculated as `quantity × unit amount`.
- [ ] Manager chooses **Save only** or **Save and send**.
- [ ] Save generates a UUID-based, signed public URL expiring exactly 48 hours after creation.
- [ ] Save and send emails the same URL shown by the manager's **Copy link** button.
- [ ] Match `users.email` case-insensitively: if an existing client is found, associate `client_id`, expose the request in the existing portal notification feed, and still send email.
- [ ] If no client exists, leave `client_id` null and send email directly to the entered address; do not auto-create a portal account.
- [ ] Opening a valid UUID records an `opened` activity with `occurred_at`; repeated opens remain auditable.
- [ ] Client enters Saudi VAT number and billing address, then pays using the embedded Moyasar card widget.
- [ ] Callback and webhooks update status; the manager page shows the current status and complete event timeline.

## Data model

- [ ] Add `payment_requests`: UUID, optional `client_id`, optional polymorphic `payable`, customer snapshot fields, title/description, quantity, `unit_amount_minor`, `total_amount_minor`, currency, billing VAT/address, status (`pending|sent|opened|paid|expired|cancelled|failed`), `expires_at`, `sent_at`, `paid_at`, creator, and timestamps.
- [ ] Store money as integer halalas and calculate totals server-side; never trust browser totals.
- [ ] Add `payment_attempts`: payment request, unique Moyasar payment ID, status, amount/currency, provider timestamps, and safe provider response data. This preserves multiple attempts without overwriting history.
- [ ] Add `payment_request_events`: request/attempt, source (`portal`, `callback`, `webhook`), unique provider event ID when supplied, event type, provider/received/processed timestamps, outcome, and JSON payload. This is the payment audit trail; do not rely on Spatie activity log alone.
- [ ] Use UUID route binding through the existing `HasUuidRouteKey` convention.
- [ ] Do not modify existing Invoice, IdeaRequest payment-upload, or quote `payment_status` flows in v1.

## Code structure

- [ ] `Admin/PaymentRequestController`: render the manager page, validate input, list/show requests, and expose copy/resend actions.
- [ ] `PublicPaymentRequestController`: validate UUID/signature/expiry, record opens, display the public page, and save VAT/address.
- [ ] `PublicPaymentAttemptController`: receive Moyasar `on_completed` data and persist the payment ID before 3-D Secure redirect.
- [ ] `MoyasarCallbackController` (invokable): fetch and verify the returned payment, synchronize status, and render the result.
- [ ] `MoyasarWebhookController` (invokable): authenticate, persist every event, and invoke idempotent synchronization.
- [ ] `CreatePaymentRequestAction`: transactionally create the request, calculate amounts, resolve client by normalized email, and write the initial event.
- [ ] `SendPaymentRequestAction`: generate the exact signed URL and queue email only after the create transaction commits.
- [ ] `RecordPaymentRequestEventAction`: single DRY path for portal, callback, and webhook audit events.
- [ ] `MoyasarClient`: the only class that calls Moyasar APIs using Laravel HTTP and the secret key.
- [ ] `PaymentStatusSynchronizer`: lock the request, verify payment ID, metadata UUID, amount, currency, and provider status, then apply one idempotent state transition.
- [ ] `PaymentRequestNotificationService`: email any address and integrate existing clients with `NotificationService`; reuse `GenericNotification` where practical.
- [ ] Form Request classes own validation; controllers remain orchestration-only.

## Views and routes

- [ ] One logged-in Blade view: `resources/views/payment-requests/index.blade.php` extending `layouts.internal-dashboard` for create, copy/send, status, and event history.
- [ ] One public Blade view: `resources/views/payment-requests/public.blade.php` as a guest page (invite-style, no dashboard chrome) for details, VAT/address, Moyasar widget, expiry, and result messages.
- [ ] Add manager routes under the existing `is_manager` group in `routes/internal.php` (manager-only; do not reuse invoice PM access).
- [ ] Add a manager nav link in `resources/views/layouts/internal-dashboard.blade.php`.
- [ ] Add throttled public UUID, billing, attempt, and callback routes in `routes/web.php`.
- [ ] Add `POST /webhooks/moyasar` with CSRF exemption in `bootstrap/app.php`, plus secret validation and throttling.
- [ ] Add i18n keys in `lang/en.json` / `lang/ar.json` and a payment notification type in `config/notifications.php`.

## Expiry and time

- [ ] Keep application/database timestamps in UTC, matching `config/app.php`; display them in the portal's configured/user locale.
- [ ] Set one immutable `expires_at = now()->addHours(48)` at creation; never use local wall-clock math for expiry.
- [ ] Public access = UUID + DB `expires_at` / status checks. Optional temporary signed URL for email/copy only; Moyasar callback cannot rely on Laravel signature params.
- [ ] Enforce expiry server-side on every public GET/POST; an expired request cannot initialize or submit payment.

## Moyasar integration and event tracking

- [ ] Configure publishable key, secret key, webhook shared secret, API base URL, currency (`config('scope.currency', 'SAR')`), and 48-hour lifetime in `config/services.php` / `.env.example`; secrets never enter views or logs.
- [ ] Pin the official form assets to `moyasar-payment-form@2.2.10` and initialize with amount in halalas (min 100), `SAR`, request metadata/UUID, supported card networks, and `methods: ['creditcard']`.
- [ ] Treat `on_completed` as “payment object created” (often still `initiated` before 3-D Secure); save the Moyasar payment ID there before redirect, then verify paid status later.
- [ ] On callback `?id=`, fetch the payment server-side and require matching `status`, amount, currency, and metadata before marking it paid.
- [ ] Register and log all events from [Available Webhooks](https://docs.moyasar.com/api/other/webhooks/available-webhooks): `payment_paid`, `payment_failed`, `payment_voided`, `payment_authorized`, `payment_captured`, `payment_refunded`, `payment_abandoned`, `payment_verified`, `card_auth_authenticated`, and `card_auth_failed`. Prefer API spelling `payment_failed` (dashboard docs also show typo `payment_faild`).
- [ ] Authenticate webhooks by body `secret_token` only; Moyasar documents no HMAC/signature header. Compare with a timing-safe check, then refetch payment before state change.
- [ ] Persist every webhook immediately with provider/received/processed timestamps, then return HTTP 2xx quickly. Moyasar retries non-2xx up to 5 more times over ~3.5 hours ([Webhook Reference](https://docs.moyasar.com/api/other/webhooks/webhook-reference)).
- [ ] Deduplicate by webhook event `id` and Moyasar payment ID; assume at-least-once delivery and no ordering guarantee.
- [ ] Preserve raw webhook payloads while excluding secrets and full card data.

## ACID, DRY, and safety

- [ ] Wrap request creation and each status transition in short database transactions with `lockForUpdate()` (same pattern as `PointsService` / invoice counters).
- [ ] Use unique UUID/payment/event constraints and duplicate-safe event insertion so retries cannot double-process payment.
- [ ] Queue email only after commit; email failure is logged and does not roll back a valid payment request.
- [ ] Extend `NotificationService::build()` for existing-client bell items; use `Notifier` / `GenericNotification` for email (existing clients and raw addresses).
- [ ] Define one status map/state-transition service used by callback and webhook.
- [ ] Accept out-of-order events in the audit log but prevent stale events from moving a terminal state backward.
- [ ] Record manager creation/send, public open, billing submission, attempt creation, callback, every webhook, duplicate, rejection, and status change.

## Files

New:
- [ ] `database/migrations/*_create_payment_requests_tables.php`
- [ ] `app/Models/PaymentRequest.php`, `PaymentAttempt.php`, `PaymentRequestEvent.php`
- [ ] `app/Http/Controllers/Admin/PaymentRequestController.php`
- [ ] `app/Http/Controllers/PublicPaymentRequestController.php`
- [ ] `app/Http/Controllers/PublicPaymentAttemptController.php`
- [ ] `app/Http/Controllers/MoyasarCallbackController.php`
- [ ] `app/Http/Controllers/MoyasarWebhookController.php`
- [ ] `app/Actions/Payments/CreatePaymentRequestAction.php`
- [ ] `app/Actions/Payments/SendPaymentRequestAction.php`
- [ ] `app/Actions/Payments/RecordPaymentRequestEventAction.php`
- [ ] `app/Services/Payments/MoyasarClient.php`
- [ ] `app/Services/Payments/PaymentStatusSynchronizer.php`
- [ ] `app/Services/Payments/PaymentRequestNotificationService.php`
- [ ] `resources/views/payment-requests/index.blade.php`
- [ ] `resources/views/payment-requests/public.blade.php`
- [ ] `tests/Feature/PaymentRequestTest.php` (mirror `ManagerPermissionsAccessTest` seeding)

Modify:
- [ ] `routes/internal.php`, `routes/web.php`, `bootstrap/app.php`
- [ ] `config/services.php`, `.env.example`, `config/notifications.php`
- [ ] `app/Services/NotificationService.php`
- [ ] `resources/views/layouts/internal-dashboard.blade.php`
- [ ] `lang/en.json`, `lang/ar.json`

## Verification

- [ ] Run `composer install` and `npm ci`, then record the clean baseline test/build result before implementation.
- [ ] Feature-test manager-only access, validation/math, save-only, save-and-send, existing/non-existing email behavior, and exact copied/emailed URL.
- [ ] Feature-test valid, tampered, and expired links; timezone boundary; repeated opens; VAT/address validation; and credit-card-only widget config.
- [ ] Mock Moyasar to test callback verification, wrong amount/currency/metadata, every webhook event, duplicate delivery, out-of-order delivery, and concurrent paid events.
- [ ] Run formatter, lint, full PHP tests, and frontend production build.
- [ ] Sandbox-check create → email/copy → open → card payment/3-D Secure → callback/webhook → manager status/timeline before linking other modules.
