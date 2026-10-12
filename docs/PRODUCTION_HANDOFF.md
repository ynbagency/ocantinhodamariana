# Production handoff
## Required facts before public launch
Approve phone, complete address, opening hours, final menu/prices, takeaway wording, privacy/legal copy and final vector identity/assets.

## Server sequence
1. Provision PHP 8.3+, Composer, MySQL 8, Nginx and TLS.
2. Create least-privilege DB and deployment users.
3. Configure `.env`; never commit it.
4. Install locked dependencies and migrate.
5. Optionally bootstrap first admin with temporary environment variables, then remove them.
6. Enable queue/scheduler and off-site backups.
7. Run `php artisan cantinho:readiness`, smoke checks and a real restore test.
8. Commission Pi/TV and complete the 72-hour soak.

## Hard launch blockers
Failed restore; no HTTPS; debug enabled; broken publication flow; Pi power/network recovery failure; unconfirmed public facts; unresolved critical security issue.
Meta is not a blocker because it is isolated from core publication.
