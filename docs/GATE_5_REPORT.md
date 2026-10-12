# Gate 5 — Editorial content and signage rotation

Implemented the first editorial content engine for the 55-inch display.

## Content families
Food, Bulgaria, Mariana, Service and Promotion. Content has scheduling, active state, duration and editorial weight.

## Signage rule
The daily `HOJE` panel remains fixed on the right. The left two-thirds rotate editorial stories. If no editorial content exists, the player uses a local brand fallback instead of a blank panel.

## Editorial mix
The builder starts from the intended 50/20/20/10 direction without forcing the operator to manage percentages. Promotion is frequency-limited by selection rather than allowed to dominate the loop.

## Media foundation
`media_assets` stores the canonical original reference, checksum and focal point. Derivative generation/upload UI remains a later gate; this gate deliberately does not fake image processing that has not run.

## Validation boundary
PHP syntax and Python signage tests can be run in this environment. Full Laravel integration tests remain blocked until Composer dependencies are installed.
