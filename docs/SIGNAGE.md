# Signage

Reference canvas: 1920×1080 landscape.

Normal states:
- Food 34% / Story 32% / Today 34%
- Promotion 66% / Today 34%
- Food feature 66% / Today 34%

The Today panel remains the information anchor.

The Pi loads localhost, not a remote page. The local agent downloads a manifest
and all required assets into a staging version, validates hashes, then atomically
switches `current`. Keep at least two valid versions.

Never expose browser/network errors on the TV.
