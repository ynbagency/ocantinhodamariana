#!/usr/bin/env bash
set -euo pipefail
fail=0
check(){ if "$@" >/dev/null 2>&1; then echo "[OK] $*"; else echo "[FAIL] $*"; fail=1; fi; }
check systemctl is-enabled cantinho-agent.service
check systemctl is-enabled cantinho-kiosk.service
check systemctl is-active cantinho-agent.service
check curl -fsS http://127.0.0.1:8765/health
check curl -fsS http://127.0.0.1:8765/version
command -v chromium >/dev/null 2>&1 || command -v chromium-browser >/dev/null 2>&1 || { echo "[FAIL] Chromium"; fail=1; }
df -h /
echo "Manual mandatory: unplug network; confirm cached content remains."
echo "Manual mandatory: hard power-cycle; confirm kiosk returns without interaction."
exit "$fail"
