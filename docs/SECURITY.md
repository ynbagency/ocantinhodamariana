# Security Baseline

HTTPS, CSRF, rate limiting, RBAC, secure password hashing, encrypted secrets,
validated uploads, audit logs, backups and least privilege.

Signage devices use unique credentials. Customer PII is never exposed through
public signage endpoints.

Loyalty uses an auditable ledger and must not use a trivially reusable static QR
to self-award rewards.
