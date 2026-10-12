# Security acceptance
Production requires HTTPS, `APP_DEBUG=false`, strong `APP_KEY`, non-sync queue, DB least privilege,
unique screen tokens, protected operator routes, off-site backups and restricted remote access.
No `.env`, tokens, database dumps or private keys may be committed.
Meta access tokens must be stored as deployment secrets, not entered into editorial content.
Security headers middleware is provided; wire it globally in the final Laravel bootstrap after
Composer/vendor installation is available and verify headers at the reverse proxy too.
