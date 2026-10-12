#!/usr/bin/env bash
set -euo pipefail
base="${1:?Usage: smoke.sh https://host}"
curl -fsS "$base/health" | grep -q '"ok":true'
for p in / /menu /contactos /takeaway; do curl -fsS "$base$p" >/dev/null; done
echo "[OK] public smoke checks"
