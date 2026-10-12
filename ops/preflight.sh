#!/usr/bin/env bash
set -euo pipefail
need(){ command -v "$1" >/dev/null || { echo "[FAIL] Missing $1"; exit 1; }; }
need php; need composer
test -f .env || { echo "[FAIL] .env missing"; exit 1; }
php -r 'exit(version_compare(PHP_VERSION,"8.3.0",">=")?0:1);'
composer validate --no-check-publish
php artisan about >/dev/null
php artisan cantinho:readiness
echo "[OK] application preflight"
