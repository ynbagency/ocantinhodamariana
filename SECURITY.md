# Security policy

Do not open public issues containing credentials, personal customer data, database dumps or screen tokens.
Rotate any secret that is accidentally exposed. Production requires HTTPS, APP_DEBUG=false, least-privilege
database credentials, unique device tokens, protected operator routes and off-site tested backups.

Secrets belong in deployment/environment secret stores, never in Git.
