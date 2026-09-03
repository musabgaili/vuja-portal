# VujaDe Portal API

Laravel backend for the **VujaDe Flutter app** (internal team first: managers, PMs, employees). This repo is a mirror of `vuja-portal` with API-first development — the old portal repo stays untouched.

## Quick start

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan serve
```

API base: `http://localhost:8000/api/v1`

## Mirror from vuja-portal (one-time)

1. Create an **empty** private repo: `musabgaili/vuja-portal-api` on GitHub.
2. Run:

```bash
chmod +x scripts/mirror-to-vuja-portal-api.sh
./scripts/mirror-to-vuja-portal-api.sh
```

## Mobile auth

- `POST /api/v1/login` — email + password
- `POST /api/v1/auth/google` — native Google ID token
- `POST /api/v1/auth/apple` — native Apple identity token

Staff accounts are **invite-only** via social login (no auto-create). Set `MOBILE_SOCIAL_AUTO_REGISTER=true` when client self-serve is ready.

## Sprint status

See `docs/API_BACKLOG_AND_SPRINT_PLAN.md` for the full backlog.

| Sprint | Status | Highlights |
|--------|--------|------------|
| 1 | Done | Auth, Google/Apple, FCM token registration, deep links |
| 2 | In progress | Dashboard, notifications, my-tasks inbox |
| 3+ | Planned | Projects, chat, meetings, approvals |

## Key env vars

| Variable | Purpose |
|----------|---------|
| `GOOGLE_CLIENT_IDS` | Allowed Google ID token audiences (web + Android + iOS) |
| `APPLE_CLIENT_IDS` | Allowed Apple identity token audiences |
| `MOBILE_APP_SCHEME` | Custom scheme (`vujade://`) |
| `MOBILE_ANDROID_SHA256` | Release cert fingerprint for App Links |
| `FIREBASE_PROJECT_ID` | FCM push |
| `FIREBASE_SERVICE_ACCOUNT_JSON` | FCM service account |
