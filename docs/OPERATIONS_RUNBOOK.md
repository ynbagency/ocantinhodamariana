# Operations runbook

## Runtime
Web: Nginx + PHP-FPM. Queue worker and scheduler are supervised by systemd.
`GET /health` checks application/database reachability.

## Deploy
Deploy to staging first. Back up before production migration. Run `ops/deploy.sh`;
it enables maintenance mode, installs locked production dependencies, migrates,
optimizes, reopens traffic and requires the health endpoint to succeed.

## Backups
Database backups must live off the application server in production. `ops/backup.sh`
is a local reference implementation, not proof of off-site backup. Restore testing
is mandatory before launch.

## Alert classes
Critical: screen offline, app health, backup/restore failure.
Warning: repeated social publishing failure, stale content, queue failure.
Restaurant operator sees business-actionable states; technical details stay with support.

## Meta
Core publication never waits for Meta. Provider jobs retry independently. No real
Graph API publication is claimed until account permissions and tokens are supplied.
