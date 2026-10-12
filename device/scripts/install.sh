#!/usr/bin/env bash
set -euo pipefail
[ "$(id -u)" -eq 0 ] || { echo "Run as root" >&2; exit 1; }
APP=/opt/cantinho
id cantinho >/dev/null 2>&1 || useradd --system --create-home --groups video,audio --shell /usr/sbin/nologin cantinho
install -d -o cantinho -g cantinho /var/lib/cantinho/releases /etc/cantinho "$APP"
cp -a "$(cd "$(dirname "$0")/../.." && pwd)"/* "$APP"/
[ -f /etc/cantinho/device.json ] || install -m 600 "$APP/device/config/device.example.json" /etc/cantinho/device.json
chown cantinho:cantinho /etc/cantinho/device.json
chmod 600 /etc/cantinho/device.json
install -m 644 "$APP/device/systemd/cantinho-agent.service" /etc/systemd/system/
install -m 644 "$APP/device/systemd/cantinho-kiosk.service" /etc/systemd/system/
systemctl daemon-reload
systemctl enable cantinho-agent.service cantinho-kiosk.service
echo "Edit /etc/cantinho/device.json, then reboot."
