# Gate 1 Status

## Implemented in source
- Daily-menu working copy and immutable publication snapshots.
- Operator validation and publish service.
- Website reads only the latest published snapshot, so editing a draft cannot silently alter public content.
- First-party website/signage channel states are marked published synchronously.
- Facebook/Instagram remain pending until real account/API configuration exists.
- Signage manifest endpoint with safe fallback.
- Mobile-first operator screen with publication status.
- Feature-test specification for publication invariants.

## Environment limitation
This workspace has PHP 8.4 but no Composer binary and outbound DNS/network is unavailable, so Laravel/Filament packages cannot be downloaded here. The framework integration tests therefore cannot truthfully be reported as executed. PHP source is linted locally.

## Next local work that does not require Composer
Continue the offline-first signage agent: manifest sync, checksum validation, atomic current/previous cache, local renderer and tests using the Python standard library.
