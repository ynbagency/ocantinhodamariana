#!/usr/bin/env bash
set -euo pipefail
file="${1:-}"
test -n "$file" && test -s "$file" || { echo "Usage: $0 backup.sql.gz"; exit 2; }
gzip -t "$file"
zcat "$file" | grep -m1 -E '^(-- MySQL dump|CREATE TABLE|INSERT INTO)' >/dev/null
echo "Backup archive integrity: OK"
echo "NOTE: this is not a restore test. Restore into an isolated database before launch."
