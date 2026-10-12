# O Cantinho da Mariana

Private application repository for the restaurant's digital ecosystem: operator backoffice, public
website/PWA, 55" digital signage, offline Raspberry Pi player, editorial/media workflow and loyalty foundation.

## Architecture
- Laravel 13 / PHP 8.3+
- MySQL 8
- Blade / Tailwind-oriented UI
- Queue + scheduler
- Raspberry Pi OS 64-bit + Chromium kiosk
- Custom offline-first Python signage agent

## Core publication flow
`Operator → immutable daily publication → website + signage manifest → Raspberry validation/cache → TV`

Meta/social publishing is intentionally asynchronous and cannot block website or signage.

## Repository map
- `app/` Laravel application/domain code
- `database/` migrations and seeders
- `resources/` Blade/UI
- `public/` public assets/PWA shell
- `device/` Raspberry player, systemd and provisioning
- `ops/` deployment, backup, preflight and smoke tooling
- `docs/` master specification, architecture, acceptance and launch runbooks
- `tests/` application test area

## Local bootstrap
This repository is a production-oriented scaffold assembled through Gate 10. It does **not** include
`vendor/`, `.env` or production credentials.

Typical target workflow:

```bash
cp .env.example .env
composer install
php artisan key:generate
# configure MySQL in .env
php artisan migrate
php artisan storage:link
php artisan serve
```

Then run the project-specific readiness check when production configuration is present:

```bash
php artisan cantinho:readiness
```

## Raspberry regression tests

```bash
cd device/agent
PYTHONPATH=. python -m unittest discover -s tests -v
```

## Production
Read these before deployment:
- `docs/PRODUCTION_HANDOFF.md`
- `docs/ACCEPTANCE_TEST_PLAN.md`
- `docs/PI_INSTALLATION_CHECKLIST.md`
- `docs/LAUNCH_RUNBOOK.md`
- `docs/SECURITY_ACCEPTANCE.md`

The release is not production-approved until the real environment passes database migrations/runtime tests,
off-site restore, HTTPS/domain checks, Raspberry/TV commissioning and the 72-hour soak test.

## Known external gates
Real Meta posting requires approved Facebook/Instagram account permissions and tokens. Final public identity,
contact details, hours, menu/prices and loyalty economics must be approved rather than inferred.

## Licence
Private/proprietary. See `LICENSE`.
