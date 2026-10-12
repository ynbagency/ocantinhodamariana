# Gate 0 Architecture Report

## Decisions
1. Custom application rather than third-party signage SaaS.
2. Laravel monolith for business platform.
3. Raspberry Pi is a separate offline-first appliance.
4. Canonical content model; destinations are adapters.
5. PWA instead of mandatory native app.
6. Loyalty ledger designed now; online ordering deferred.
7. PT-PT first, localisation-ready.
8. No paid dependency is required for the core signage path.

## Genuine external blockers
These do not block core development:
- final domain and production server credentials;
- final telephone, opening hours, menu and prices;
- final vector identity;
- Meta account/API permissions;
- final loyalty reward economics.

## Risks
- consumer TV power/input behaviour differs by model: test physical TV.
- social APIs can change: isolate behind adapters and retain manual fallback.
- SD corruption: prefer high-endurance media/SSD and resilient filesystem.
- operator complexity: keep advanced settings out of restaurant role.
- ordering can create missed orders: keep disabled until acceptance workflow exists.

## Gate 0 outcome
Architecture is suitable to proceed to Gate 1.
