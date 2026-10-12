# Deployment

Environments: local, staging, production.

CI gates: install → static checks → tests → frontend build → security checks →
staging → smoke tests → manual production approval → deploy → health verification.

Backups must be off-server and restore-tested.
