# Gate 3 — End-to-end signage control plane

Implemented locally:
- per-screen identity and SHA-256 token storage;
- authenticated manifest/release/heartbeat endpoints;
- device ID must match the authenticated screen;
- immutable published menu rendered to a deterministic signage HTML release;
- SHA-256 supplied in manifest and validated by the Pi before activation;
- heartbeat reports player/content version, last successful sync and error;
- server records last-seen state for Online/Warning/Offline classification;
- social publishing remains isolated and does not affect TV/site publication.

Not claimed as physically validated: TV HDMI/power restoration, Pi kernel watchdog and abrupt mains-loss resilience.

Validation performed in the local build environment:
- PHP syntax validation: passed for application/migrations/routes.
- Python signage store tests: passed with agent package path configured.
- Python agent compilation: passed.
