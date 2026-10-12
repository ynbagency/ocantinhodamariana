# Raspberry Pi Appliance

## Boot contract
Power → OS → local agent → graphical session → Chromium kiosk → last-known-good
content → background sync.

WAN connectivity must not block kiosk startup.

## Recovery
- systemd restarts agent/browser.
- hardware/kernel watchdog reboots a stuck device.
- resilient/read-only root strategy where compatible.
- explicit versioned releases and rollback.
- no uncontrolled distribution upgrades.

## Remote support
No public SSH port. Keys only. Unique device credentials. Least privilege.
