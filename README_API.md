# VujaDe Portal API

Laravel backend for the **VujaDe Flutter app** (internal team first: managers, PMs, employees).

**Primary repository:** [github.com/musabgaili/vuja-portal-api](https://github.com/musabgaili/vuja-portal-api)

The legacy web portal lives in `vuja-portal` and is not modified by API work here.

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

## Push to GitHub (first time)

After creating the empty `vuja-portal-api` repo and granting this Cloud Agent access to it:

```bash
./scripts/mirror-to-vuja-portal-api.sh
```

## Sprint status

| Sprint | Status | Highlights |
|--------|--------|------------|
| 1 | Done | Auth, Google/Apple, FCM tokens, deep links |
| 2 | Done | Dashboard, notifications, my-tasks |
| 3 | Done | Projects list/detail, milestones, tasks, kanban |
| 4 | Done | Chat channels, DMs, messages, reactions, mentions |
| 5+ | Planned | Meetings, approvals, financials |

Full backlog: `docs/API_BACKLOG_AND_SPRINT_PLAN.md`
