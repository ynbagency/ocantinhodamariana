# Gate 9 — QA, commissioning and release readiness

Delivered:
- Operator-role middleware baseline and role migration.
- Security headers middleware.
- Production readiness Artisan command.
- Backup archive verifier.
- Raspberry field commissioning checker.
- Full acceptance plan covering daily publish, offline, failure isolation, security, restore and physical TV.
- Pi installation checklist.
- Launch runbook and security acceptance requirements.
- CI/static validation retained from Gate 8.

Important execution boundary:
This environment still lacks installed Laravel vendor dependencies and real production infrastructure.
Therefore middleware registration/runtime Laravel tests, real DB migrations, restore, HTTPS, Meta,
physical TV/Pi and 72-hour soak are not claimed as executed. Gate 9 provides executable checks and
field procedures for those real-environment acceptance steps.
