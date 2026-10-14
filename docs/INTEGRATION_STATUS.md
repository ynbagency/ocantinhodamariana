# Integration status — 2026-10-12

This report supersedes the historical Gate reports for current runtime evidence. It is not a production sign-off.

Update on 2026-10-13: [Daily operator workflow](DAILY_OPERATION.md) adds yesterday-copy, explicit draft availability, one-tap published availability and same-day scheduled publication. Current-form publication is atomic; existing drafts are preserved by availability changes; schedule execution is locked and idempotent. A tenth migration adds scheduling.

Backoffice update: [Access and navigation](BACKOFFICE.md) adds safe account provisioning, a shared responsive navigation layout, actionable dashboard alerts and isolated browser verification. A provisional local admin is configured; its password is only in private ignored storage.

## Completed

- Composer dependencies installed and locked; Laravel 13.35.0 boots on PHP 8.3.14.
- Restaurant view provider, operator middleware, security headers and authenticated heartbeat CSRF exception registered.
- Login throttled; private screens marked no-store; service workers only handle an explicit public-page allowlist and purge old Cantinho caches.
- Duplicate role migration fixed; sessions and failed-job tables added. All nine migrations passed on an isolated local SQLite database and in-memory test databases.
- Daily-menu service dates stored as date-only values; editing a published day no longer attempts a duplicate insert on SQLite.
- Homepage and Today routes use the current public layout. Takeaway Blade compilation fixed. Dashboard shows the published snapshot rather than a draft.
- Signage HTML and media persisted as SHA-256-addressed immutable blobs. Editorial/media changes change bundle version; old download URLs retain their original bytes.
- Missing editorial media removed from the generated playlist.
- Local player watches its own version endpoint every five seconds and reloads after activation, including first-boot fallback. Network failure leaves current display intact.
- Agent rejects unsafe manifest paths/versions, foreign asset origins and authenticated redirects. Invalid device configuration now fails visibly instead of silently switching to unconfigured values.
- Device configuration readable only by the service user; screen-age calculation corrected.
- CI installs locked dependencies and runs Laravel, Node and Linux signage tests.

## Validation

- PHPUnit (2026-10-13): 22 tests, 233 assertions passed, using SQLite in memory.
- Node: 4 tests passed (private-cache exclusion, public offline retrieval, old-cache purge, local player reload).
- Python: 7 tests passed; 3 symlink activation tests explicitly skipped on Windows and still required on Linux.
- Every operational/device shell script passed individual Bash syntax checks with Git Bash.
- PHP lint passed for 70 PHP files; `git diff --check` passed.
- Isolated Chrome browser test passed login, shared navigation, current-form publication, sold-out state and logout at 390×844. No horizontal overflow at 320, 768 and 1280 pixels. Mobile screenshots reviewed; the primary daily action is visible above the fixed navigation.
- The scheduling migration passed on local SQLite. The local scheduler executed `cantinho:publish-scheduled` successfully during a real minute tick.
- Actual local HTTP requests to `/`, `/hoje`, `/menu`, `/contactos`, `/takeaway` and `/health` returned 200.
- Composer validation passed. Composer's dependency advisory check reported no known vulnerabilities during installation.

Commands:

```bash
php vendor/phpunit/phpunit/phpunit
node --test tests/service-worker.test.cjs
PYTHONPATH=device/agent python3 -m unittest discover -s device/agent/tests -v
```

## Local environment

The working copy has a private, ignored `.env`, generated application key and `database/local.sqlite`. Local queue is synchronous and cache is file-based; this is solely a development environment. No production credentials or customer data were configured. The local provisional admin is documented in `BACKOFFICE.md`; use `cantinho:create-user` to provision approved operator identities.

## Still required

- Production-like MySQL/Redis tests and staging deployment.
- Actual Raspberry/Chromium commissioning, full Linux regression tests, power/network/crash tests and 72-hour soak.
- Final graphical-session permissions, watchdog and resilient filesystem provisioning.
- Off-site database/media backups, restore test and alert delivery.
- Immutable signage blobs currently retain historical files indefinitely; define a retention/cleanup policy before sustained production use, preserving in-flight downloads.
- Meta Graph API implementation, social asset generation, idempotency/status handling and approved account permissions. Tokens alone do not enable publication.
- Full customer loyalty lifecycle and explicit consent workflow.
- Permanent menu, future-day preparation, final approved business facts and brand assets, browser visual/accessibility QA.

The source role-index migration was corrected because this repository was not evidenced as deployed. Any previously modified production schema must be inspected before applying migrations.
