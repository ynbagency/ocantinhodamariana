#!/usr/bin/env bash
set -euo pipefail
: "${DB_DATABASE:?}" "${DB_USERNAME:?}" "${BACKUP_DIR:=/var/backups/cantinho}"
mkdir -p "$BACKUP_DIR"; stamp=$(date +%Y%m%d-%H%M%S)
mysqldump --single-transaction --quick -u"$DB_USERNAME" ${DB_PASSWORD:+-p"$DB_PASSWORD"} "$DB_DATABASE" | gzip > "$BACKUP_DIR/db-$stamp.sql.gz"
find "$BACKUP_DIR" -type f -name 'db-*.sql.gz' -mtime +"${BACKUP_RETENTION_DAYS:-14}" -delete
echo "Backup created: db-$stamp.sql.gz"
