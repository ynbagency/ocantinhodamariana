#!/usr/bin/env bash
set -euo pipefail
ROOT=${1:-/var/lib/cantinho}
mapfile -t R < <(find "$ROOT/releases" -mindepth 1 -maxdepth 1 -type d -printf '%T@ %p\n' | sort -nr | awk '{print $2}')
[ ${#R[@]} -ge 2 ] || { echo "No previous release" >&2; exit 1; }
ln -sfn "${R[1]}" "$ROOT/.current-rollback"
mv -Tf "$ROOT/.current-rollback" "$ROOT/current"
echo "Rolled back to ${R[1]}"
