#!/usr/bin/env bash
set -euo pipefail
php artisan down --retry=30 || true
trap 'php artisan up || true' EXIT
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
php artisan migrate --force
php artisan optimize
php artisan storage:link || true
php artisan up
trap - EXIT
curl --fail --silent "${APP_URL%/}/health" >/dev/null
echo "Deployment health check OK"
