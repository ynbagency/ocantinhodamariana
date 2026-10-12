# Architecture

## Core rule
One canonical database. Channel-specific renderers and adapters consume it.

## Boundaries
- Menu: menu catalogue and daily menu.
- Publishing: versioned publication and destination attempts.
- Signage: screen registration, manifest, heartbeat and health.
- Loyalty: customer account, immutable transaction ledger and rewards.
- Future Orders: separate bounded context; disabled until operational workflow exists.

## Failure isolation
Social failure never rolls back website or signage publication.
The Pi never depends on WAN availability to render its current content.

## Initial deployment
Nginx → Laravel → MySQL + Redis + queue worker + scheduler + media storage.

No Kubernetes.
