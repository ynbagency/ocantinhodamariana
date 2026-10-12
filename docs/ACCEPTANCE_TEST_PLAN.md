# Gate 9 acceptance test plan

## A. Daily operation
1. Operator logs in on phone.
2. Creates/edits today's dishes and saves draft.
3. Confirm public site and TV remain on previous published snapshot.
4. Preview TV/site.
5. Publish.
6. Confirm site changes and Pi receives same release.
7. Mark a dish unavailable and republish.
8. Confirm no duplicate social dispatch for the same idempotent publication event.

## B. Offline signage
1. Sync a valid release.
2. Disconnect Ethernet/Wi-Fi for 30 minutes: content must continue.
3. Reconnect: heartbeat and sync recover automatically.
4. Supply a deliberately bad checksum in staging: current release must remain.
5. Abruptly remove Pi power, restore it: kiosk must return without login/click.
6. Interrupt download mid-release: previous release must remain.

## C. Failure isolation
- Disable Meta: website and TV publish normally.
- Stop queue worker: core synchronous publication remains usable; pending jobs recover after worker restart.
- Temporarily stop central server after Pi sync: TV continues last-known-good.
- Simulate Chromium crash: service supervision must restore kiosk.

## D. Security
- Anonymous access to `/operacao/*` denied.
- Operator cannot reach admin-only configuration when introduced.
- Screen token A cannot fetch manifest for screen B.
- Uploaded non-image/oversized files rejected.
- Production debug disabled; HTTPS enforced at proxy; secrets absent from repository.
- Verify database least-privilege user and rotate installation credentials.

## E. Backup/recovery
- Automated backup exists off-server.
- `verify-backup.sh` passes.
- Restore latest backup into isolated database.
- Verify row counts/key tables and login after restore.
- Record RPO/RTO observed; do not infer them.

## F. Physical 55" acceptance
From intended viewing distance verify dish names, prices and `HOJE`.
Test HDMI input restoration, TV auto-power after mains return, no overscan, no screensaver, 72-hour soak.

## Sign-off evidence
Record date, tester, build commit, Pi serial/device ID, browser/player versions, photos of TV,
failure tests performed, result and unresolved defects.
