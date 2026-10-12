# Gate 2 — Offline-first Signage Player

Implemented locally:
- authenticated manifest client;
- SHA-256 validation for every downloaded asset;
- staging directory and atomic `current` symlink activation;
- last-known-good rendering with no WAN dependency;
- two-release retention and explicit rollback script;
- local `/health` and `/version` endpoints;
- background sync isolated from the HTTP kiosk server;
- safe built-in fallback when no release has ever been downloaded;
- example unique device configuration;
- systemd/install scaffolding;
- unit tests for successful activation and failed-checksum non-activation.

Not claimed as physically validated: TV HDMI recovery, Raspberry Pi watchdog/kernel behaviour,
power-loss filesystem behaviour and real WAN/API integration require the actual device/server.
