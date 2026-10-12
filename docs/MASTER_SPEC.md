# O CANTINHO DA MARIANA

## Master Product, Brand & Digital Signage Specification

**Version:** 1.0\
**Date:** 2026-10-05\
**Status:** Implementation specification / Codex hand-off\
**Primary principle:** Publish once. Distribute everywhere. Operate with
near-zero technical intervention.

------------------------------------------------------------------------

# 1. Executive objective

Build a single digital operating platform for **O Cantinho da Mariana**
that combines:

1.  a strong, simple and empathetic restaurant brand;
2.  a public website;
3.  a customer PWA (installable web app);
4.  a radically simple restaurant back office;
5.  a 55" in-store digital signage experience;
6.  automatic content reuse across TV, website and social channels;
7.  promotions for takeaway, reservations/contact and future services;
8.  a customer loyalty foundation;
9.  a future-ready ordering system;
10. a Raspberry Pi signage appliance designed for unattended, zero-touch
    operation.

The system must not become another piece of technology that restaurant
staff have to manage.

The operational model is:

> **Enter information once → press Publish → TV, website and eligible
> social channels update from the same source.**

The television must remain useful during an Internet outage and the
Raspberry Pi must recover automatically from ordinary power, browser,
network and operating-system failures.

------------------------------------------------------------------------

# 2. Product principles

## 2.1 Zero-touch restaurant operation

The restaurant operator must never need to:

-   SSH into the Raspberry Pi;
-   configure the television;
-   clear a browser cache;
-   restart Chromium manually;
-   upload the same content to several destinations;
-   edit HTML;
-   understand a CMS;
-   understand social APIs;
-   manually rebuild the TV screen.

Normal daily operation should be possible from a smartphone.

## 2.2 One source of truth

Menu items, daily dishes, prices, photographs, promotions, cultural
content, opening information and contact information live in one
database.

Every channel renders from this canonical data.

## 2.3 Graceful degradation

Failure of one external service must not stop the rest of the platform.

Examples:

-   Instagram failure must not prevent the daily menu from publishing.
-   Internet failure must not blank the TV.
-   Social publishing failure must create a visible retry/error state in
    the admin.
-   A broken image must never break the signage layout.
-   An unavailable API must be isolated behind an adapter.

## 2.4 Progressive complexity

Day-one operation is deliberately simple.

Advanced ordering, loyalty campaigns, push notifications and richer CRM
are designed into the data model but are activated only when
operationally justified.

------------------------------------------------------------------------

# 3. Scope

## Phase A --- Foundation and brand

Deliver:

-   brand strategy;
-   visual identity;
-   logo system;
-   typography;
-   colour system;
-   iconography;
-   photography direction;
-   tone of voice;
-   menu visual language;
-   TV design language;
-   social templates;
-   website/PWA design system;
-   basic brand guidelines.

## Phase B --- Core platform

Deliver:

-   Laravel application;
-   database;
-   administration panel;
-   media library;
-   menu and daily-menu management;
-   promotion management;
-   Bulgaria/cultural-content management;
-   website;
-   TV signage web application;
-   publication workflow;
-   audit log.

## Phase C --- Raspberry Pi appliance

Deliver:

-   provisionable Raspberry Pi image/configuration;
-   Chromium kiosk;
-   local offline fallback;
-   watchdogs;
-   automatic restart;
-   health heartbeat;
-   remote support path;
-   hardening;
-   recovery documentation.

## Phase D --- Distribution

Deliver:

-   website publication;
-   TV publication;
-   social publishing adapters;
-   generated social assets;
-   publication queue;
-   retries and error reporting.

## Phase E --- Customer PWA and loyalty

Deliver:

-   installable PWA;
-   customer registration/login;
-   digital loyalty identity/QR;
-   visits/purchases/points ledger;
-   rewards;
-   customer history;
-   privacy/consent controls.

## Phase F --- Future ordering

Architect now, activate later:

-   product catalogue/orderability;
-   basket;
-   pickup slot;
-   order placement;
-   restaurant acceptance/rejection;
-   status;
-   payment integration adapter;
-   notifications.

Telephone ordering remains a first-class CTA from launch.

------------------------------------------------------------------------

# 4. Recommended technology stack

## Application

-   **Backend:** Laravel 13, PHP 8.3+
-   **Admin:** Filament 5
-   **Database:** MySQL 8.x
-   **Cache / queues:** Redis recommended; database queue acceptable for
    the smallest deployment
-   **Web server:** Nginx
-   **Frontend build:** Vite
-   **Public frontend:** Laravel + Blade/Livewire where possible
-   **Interactive PWA:** Livewire/Alpine or a lightweight Vue layer only
    where it materially improves UX
-   **CSS:** Tailwind CSS
-   **Storage:** S3-compatible object storage in production; local
    storage supported in development
-   **Image processing:** server-side generation of derivatives (TV,
    web, social)
-   **Authentication:** Laravel-native auth; separate roles/permissions
-   **Scheduler:** Laravel scheduler
-   **Queue worker:** Supervisor/systemd
-   **Testing:** Pest/PHPUnit + browser/E2E tests
-   **Deployment:** Git-based CI/CD to an Ubuntu server
-   **Monitoring:** application health endpoint + structured logs +
    Raspberry heartbeat

Avoid unnecessary SaaS dependencies.

## Raspberry Pi

Preferred hardware:

-   Raspberry Pi 5, 4 GB RAM (Pi 4 is acceptable)
-   official/reliable PSU
-   quality microSD, preferably high-endurance, or USB SSD where
    practical
-   micro-HDMI/HDMI cable as required
-   Ethernet preferred; Wi-Fi supported
-   ventilated enclosure

Software:

-   Raspberry Pi OS 64-bit
-   Chromium
-   kiosk user with minimum privileges
-   systemd-managed kiosk service
-   hardware/kernel watchdog
-   read-only/overlay root filesystem after provisioning
-   local signage shell/fallback assets
-   secure remote-access mechanism without public inbound router ports

**Do not add Anthias unless a later requirement proves it necessary.**
The custom signage page is the signage player; fewer layers improve
reliability.

------------------------------------------------------------------------

# 5. High-level architecture

``` text
                       ┌──────────────────────┐
                       │   ADMIN / MOBILE     │
                       │ Restaurant operator  │
                       └──────────┬───────────┘
                                  │
                                  ▼
                    ┌──────────────────────────┐
                    │      LARAVEL CORE        │
                    │ canonical content + API  │
                    └─────┬────────┬───────────┘
                          │        │
              ┌───────────┘        └─────────────┐
              ▼                                  ▼
      ┌───────────────┐                  ┌─────────────────┐
      │ Public website│                  │ Publishing queue │
      │ + Customer PWA│                  └────────┬────────┘
      └───────────────┘                           │
                                                 ▼
                                     ┌──────────────────────┐
                                     │ Social adapters      │
                                     │ Meta / future        │
                                     └──────────────────────┘

                          Laravel content/API
                                  │
                                  ▼
                      ┌──────────────────────┐
                      │ Raspberry Pi player  │
                      │ local cache + kiosk  │
                      └──────────┬───────────┘
                                 │ HDMI
                                 ▼
                         ┌──────────────┐
                         │ 55" TV       │
                         └──────────────┘
```

------------------------------------------------------------------------

# 6. Information model

Minimum entities:

## Restaurant

-   name
-   address
-   telephone
-   email
-   map coordinates
-   opening hours
-   takeaway availability
-   social links
-   locale/timezone
-   legal/footer data

## MenuCategory

Examples: soups, grills, meat, fish, Bulgarian specialities, desserts,
drinks.

## MenuItem

-   name
-   short description
-   long description
-   category
-   price
-   allergens
-   dietary attributes
-   image(s)
-   active
-   featured
-   available for takeaway
-   future: orderable
-   future: stock/availability

## DailyMenu

-   service date
-   optional service period
-   title
-   soup
-   dishes/items
-   sides
-   dessert
-   drink/inclusions
-   price(s)
-   notes
-   status: draft / scheduled / published / expired
-   publish_at
-   expire_at
-   social publishing options

Daily menus must support selecting existing MenuItems **and** entering
quick one-off dishes.

## MediaAsset

-   original
-   type
-   alt text
-   crop metadata
-   focal point
-   derivatives
-   copyright/source if applicable
-   tags

## Promotion

Examples:

-   takeaway;
-   phone ordering;
-   group meals;
-   Bulgarian speciality week;
-   dessert promotion;
-   seasonal event.

Fields:

-   title
-   short message
-   CTA
-   optional QR
-   media
-   active period
-   priority
-   channels
-   TV duration
-   scheduling rules

## Story / BulgariaContent

-   title
-   short copy
-   long copy
-   image/gallery
-   source/rights metadata
-   category: culture / food / place / tradition / fact
-   language
-   active
-   TV-safe version

## Publication

Records every requested publication:

-   content object
-   version
-   destination
-   requested_at
-   completed_at
-   status
-   external ID
-   retry count
-   error
-   rendered asset

## Screen

-   name
-   installation
-   orientation
-   resolution
-   device ID
-   last heartbeat
-   last content sync
-   app version
-   IP metadata where appropriate
-   online/offline status
-   assigned layout

## Customer

-   minimal identity data
-   telephone/email as configured
-   consent state
-   loyalty identifier
-   QR token
-   created_at

## LoyaltyAccount / LoyaltyTransaction

Use a **ledger**, never just an editable points total.

Transaction types:

-   earn
-   redeem
-   adjustment
-   reversal
-   expiry

Store reason, operator, timestamp and reference.

## Reward

-   name
-   threshold/cost
-   validity
-   active
-   redemption rules

## Future Order domain

Create interfaces/schema boundaries now without exposing unfinished
ordering UI.

------------------------------------------------------------------------

# 7. Restaurant back office

The daily operator experience is the highest-priority UX.

## Home screen

On mobile, the first screen should contain:

### Primary action

**PRATOS DE HOJE**

Large touch target.

Display:

-   today's current status;
-   dishes currently published;
-   last publication time;
-   one large **Editar** action.

### Secondary actions

-   Fotografias
-   Promoções
-   Menu
-   Conteúdos Bulgária
-   Clientes/Fidelização
-   Ver TV
-   Ver Site

Technical/configuration functions must be hidden from the normal
operator role.

## Daily-menu flow

Target: a competent first-time user can publish today's menu in **under
60 seconds**.

Flow:

1.  Tap `Pratos de hoje`.
2.  Choose an existing dish or type a new one.
3.  Add price if needed.
4.  Add second/third dish if applicable.
5.  Optionally choose photo.
6.  Preview.
7.  Tap **PUBLICAR AGORA**.

Confirmation should say clearly where it went:

``` text
Publicado

✓ Televisão
✓ Site
✓ Facebook
✓ Instagram

18:42
```

If a destination fails:

``` text
Publicado na televisão e no site.

Instagram não publicou.
[Tentar novamente]
```

Never roll back successful destinations because another destination
failed.

## Scheduling

Support:

-   publish now;
-   schedule date/time;
-   duplicate yesterday/previous menu;
-   recurring promotion windows;
-   automatic daily-menu expiry.

## Safety

Before publication:

-   validate price format;
-   warn if no dish exists;
-   warn if publication date is wrong;
-   never require a social account for core publication;
-   preview TV and social crop.

------------------------------------------------------------------------

# 8. Digital signage

## 8.1 Display target

Primary:

-   55" television
-   landscape
-   16:9
-   design baseline: 1920×1080
-   must scale cleanly to 4K

## 8.2 Three-zone concept

The requested visual concept is three vertical territories, but it must
not become three rigid newspaper columns.

Recommended composition:

``` text
┌────────────────────────────────────────────────────────────┐
│                                                            │
│   FOOD / BRAND         BULGARIA / STORY      TODAY         │
│   dynamic imagery      dynamic story          persistent    │
│   + promotions         + visual content       menu          │
│                                                            │
│   approx. 33%          approx. 33%            approx. 34%   │
│                                                            │
└────────────────────────────────────────────────────────────┘
```

### Left zone --- Food / commercial

Rotating:

-   high-quality dish photography;
-   takeaway promotion;
-   house specialities;
-   restaurant services;
-   seasonal promotion.

### Centre zone --- Bulgaria / storytelling

Rotating:

-   Bulgarian places;
-   culinary traditions;
-   ingredients;
-   cultural details;
-   short human stories.

No long paragraphs.

### Right zone --- Today

Usually persistent:

-   `HOJE`
-   soup if applicable
-   daily dishes
-   price
-   concise inclusions
-   optional small CTA

This is the information customers must be able to read quickly from the
room.

## 8.3 Motion

Motion should feel polished, not like a PowerPoint slideshow.

Rules:

-   subtle crossfades;
-   slow image movement where appropriate;
-   no aggressive wipes;
-   no flashing;
-   no tiny text;
-   no audio by default;
-   transitions should never make the daily menu unreadable.

## 8.4 Advertising

Promotion cards are first-class content.

Examples:

**LEVE O CANTINHO CONSIGO**\
Takeaway disponível\
`Encomendas: [telefone]`

**HOJE NÃO LHE APETECE COZINHAR?**\
Nós tratamos do jantar.

Promotions may temporarily occupy the left/centre areas or, for
high-priority campaigns, use a short full-screen takeover while
preserving sensible frequency caps.

## 8.5 QR

Use QR sparingly.

Possible TV QR targets:

-   customer PWA;
-   loyalty enrolment;
-   menu;
-   directions/contact.

Never require scanning a QR to discover the daily menu while physically
inside the restaurant.

------------------------------------------------------------------------

# 9. Signage delivery and offline strategy

This is a critical engineering requirement.

## Do not rely only on a remote webpage

The Pi must have a **local player shell**.

Recommended pattern:

1.  Chromium loads a local URL such as `http://127.0.0.1:PORT`.
2.  A tiny local signage service owns the cached display package.
3.  It periodically calls the central API.
4.  New content is downloaded to a staging version.
5.  Assets are checksummed.
6.  Only after the full package validates is it atomically promoted to
    `current`.
7.  If sync fails, `current` remains untouched.
8.  The screen therefore always has a last-known-good presentation.

Cache at least:

-   layout;
-   CSS/JS;
-   daily menu JSON;
-   promotions;
-   Bulgaria stories;
-   required images/video;
-   brand assets.

The screen must be capable of continuing for days without Internet using
the last valid content.

## Cache versioning

Example:

``` text
/signage-cache/
  current -> versions/2026-10-05T17-40-12Z/
  versions/
    ...
```

Keep at least the last two valid versions for rollback.

## Content freshness indicator

Do **not** expose technical warnings to restaurant customers during a
short outage.

Admin should see:

-   screen online/offline;
-   last heartbeat;
-   last successful content sync;
-   content version.

After a configurable long stale period, notify the administrator, not
the dining room.

------------------------------------------------------------------------

# 10. Raspberry Pi zero-touch appliance requirements

## Boot

After power restoration:

1.  Pi powers on automatically.
2.  OS boots.
3.  network attempts to connect but does not block signage startup
    indefinitely;
4.  local signage service starts;
5.  graphical session starts;
6.  Chromium starts fullscreen in kiosk mode;
7.  last-known-good content displays;
8.  background sync starts;
9.  heartbeat is sent when connectivity exists.

No keyboard, mouse or staff intervention.

## Browser recovery

The Chromium process must be supervised.

If it exits/crashes:

-   automatic restart within seconds.

If rendering becomes unhealthy:

-   local health check triggers controlled Chromium restart.

## Application recovery

The local signage service must use:

-   `Restart=always` or equivalent;
-   restart backoff;
-   health checks;
-   logs with rotation.

## OS recovery

Enable the Raspberry Pi hardware/kernel watchdog.

If userspace/OS stops servicing the watchdog, the Pi reboots.

## Filesystem resilience

Production configuration should use a read-only/overlay root filesystem
where compatible with the final updater design.

Persistent mutable state must be deliberately separated.

Goal:

-   abrupt power loss should not normally corrupt the bootable OS;
-   ordinary power cycling should be safe.

## Updates

Do not perform uncontrolled unattended distribution upgrades.

Use explicit, tested application releases.

Suggested release strategy:

-   versioned signage bundle;
-   signed/checksummed release metadata;
-   download;
-   validate;
-   switch;
-   health check;
-   rollback on failure.

OS security maintenance must have a defined maintenance process.

## TV behaviour

Where supported:

-   configure TV to restore previous power state / power on after AC
    return;
-   disable sleep/eco shutdown that would unexpectedly blank the
    display;
-   select persistent HDMI input;
-   disable consumer overlays where possible.

CEC control may be added but must not be the only recovery mechanism.

------------------------------------------------------------------------

# 11. Remote administration

Remote access is break-glass support, not normal operation.

Requirements:

-   no public SSH port forwarding;
-   SSH keys only;
-   password SSH disabled;
-   least-privilege user;
-   optional private overlay network/VPN solution;
-   unique credentials per device;
-   audit support actions where practical.

Admin dashboard should solve most diagnosis without SSH:

-   heartbeat;
-   online/offline;
-   uptime;
-   player version;
-   content version;
-   last sync;
-   disk status;
-   network status;
-   last player restart;
-   screenshot/visual confirmation only if implemented securely and
    privacy-reviewed.

------------------------------------------------------------------------

# 12. Public website

The site should be compact, fast and conversion-oriented.

## Primary navigation

-   Hoje
-   Menu
-   O Cantinho
-   Bulgária à Mesa
-   Takeaway
-   Contactos

## Homepage priority

Above the fold:

1.  restaurant identity;
2.  today's food / strong food image;
3.  **Ver pratos de hoje**;
4.  **Ligar / Encomendar**.

Then:

-   today's menu;
-   selected menu;
-   takeaway;
-   story;
-   Bulgarian identity;
-   location/hours;
-   loyalty/PWA CTA.

## SEO

Implement:

-   semantic HTML;
-   restaurant structured data;
-   Menu/MenuItem structured information where appropriate and valid;
-   Open Graph;
-   canonical URLs;
-   sitemap;
-   robots;
-   performant images;
-   local search fundamentals.

Never invent reviews, awards, provenance or food claims.

------------------------------------------------------------------------

# 13. Customer PWA

The customer product is a **Progressive Web App**, not a mandatory
native iOS/Android app.

Advantages:

-   opened from a URL;
-   installable on supported devices;
-   one codebase;
-   no app-store dependency for launch;
-   can evolve into richer functionality.

## Guest mode

Without account:

-   today's dishes;
-   menu;
-   opening hours;
-   call;
-   directions;
-   takeaway information;
-   cultural content.

## Member mode

With account:

-   loyalty card/QR;
-   points/visits;
-   available rewards;
-   activity history;
-   profile;
-   communication preferences.

## Future

-   order ahead;
-   pickup time;
-   order history;
-   favourites;
-   notifications;
-   offers.

Do not require account creation merely to see menu/prices.

------------------------------------------------------------------------

# 14. Loyalty design

The loyalty system must be operationally simple and resistant to casual
abuse.

## MVP option

Customer has a rotating/secure loyalty QR in the PWA.

Restaurant operator scans or identifies the customer when the qualifying
transaction occurs.

Alternative: restaurant presents a controlled transaction QR that
customer scans.

Do **not** implement a static publicly reusable QR that grants unlimited
points.

## Rules engine

Support configurable:

-   points per euro;
-   points per visit;
-   item-specific rewards;
-   minimum spend;
-   reward thresholds;
-   validity;
-   manual adjustment with audit trail.

Start with one simple mechanic. The platform may support more, but the
customer-facing programme should not launch complicated.

------------------------------------------------------------------------

# 15. Ordering roadmap

Ordering is **not required for MVP launch**, but architecture must avoid
a rewrite.

## Launch

CTAs:

-   `Ligar para encomendar`
-   optional WhatsApp only if the business chooses it operationally
-   takeaway information

## Later digital ordering

State machine:

``` text
DRAFT
→ PLACED
→ PENDING_ACCEPTANCE
→ ACCEPTED
→ PREPARING
→ READY
→ COLLECTED

Alternative terminal states:
REJECTED / CANCELLED / EXPIRED
```

The restaurant must never silently receive orders in a channel staff do
not monitor.

Before online ordering is enabled, require:

-   unmistakable incoming-order alert;
-   acceptance workflow;
-   timeout behaviour;
-   sold-out controls;
-   opening/service windows;
-   capacity controls.

------------------------------------------------------------------------

# 16. Social distribution

Build social publishing as an adapter layer.

``` text
SocialPublisher
  ├── FacebookPublisher
  ├── InstagramPublisher
  └── FuturePublisher
```

A publication job should:

1.  create channel-specific copy;
2.  create the correct image derivative/template;
3.  validate account/channel prerequisites;
4.  publish;
5.  store external result/ID;
6.  retry transient failures;
7.  expose permanent failures to admin.

The canonical restaurant content remains independent of Meta or any
other network.

**Important:** direct publishing capabilities and permissions depend on
the account type and the current platform API rules. Validate the actual
restaurant accounts during integration; do not hard-code assumptions
into the product specification.

Provide a manual fallback:

-   generated post image;
-   generated caption;
-   `Copiar texto`;
-   `Descarregar imagem` / share workflow where appropriate.

Thus a social API policy change does not break daily restaurant
operation.

------------------------------------------------------------------------

# 17. Branding brief

## Brand

**O Cantinho da Mariana**

## Desired character

-   welcoming;
-   human;
-   confident;
-   uncomplicated;
-   contemporary;
-   accessible;
-   appetising;
-   recognisable;
-   warm without becoming rustic cliché;
-   Bulgarian influence without turning the identity into a souvenir
    shop.

## McDonald's reference --- interpretation

Use the reference only for principles:

-   immediate recognition;
-   high legibility;
-   disciplined visual system;
-   clear hierarchy;
-   repeatable components;
-   simple calls to action;
-   strong consistency across physical and digital touchpoints.

**Do not imitate McDonald's logo, trade dress, colour arrangement,
typography or proprietary visual assets.**

## Identity direction

Recommended creative territory:

**"A casa próxima, com uma história que veio de longe."**

The brand should reconcile:

-   a neighbourhood restaurant in Portugal;
-   Mariana as a human centre;
-   Bulgarian cultural roots;
-   grilled/comforting food;
-   everyday accessibility.

Avoid:

-   literal Bulgarian-flag branding as the whole identity;
-   faux-folklore overload;
-   generic chef hats;
-   generic fork-and-knife logos;
-   flames as the dominant cliché;
-   excessive red/yellow fast-food mimicry;
-   pseudo-luxury black/gold.

## Logo system requirements

Create:

1.  primary wordmark;
2.  compact mark;
3.  horizontal lockup;
4.  vertical lockup;
5.  monochrome version;
6.  reversed version;
7.  favicon/app icon;
8.  social avatar;
9.  signage-safe version.

The mark must work at:

-   PWA icon size;
-   social avatar;
-   printed menu;
-   TV corner bug;
-   storefront/signage.

## Typography

Select:

-   one highly legible display family;
-   one text/UI family or a variable family that can perform both roles.

Must support Portuguese and Bulgarian/Cyrillic characters if Bulgarian
text is ever displayed.

## Colour

Create a proprietary palette based on food, warmth and contemporary
hospitality.

Deliver tokens:

``` text
brand-primary
brand-secondary
accent
background
surface
text-primary
text-muted
success
warning
danger
```

Meet WCAG contrast requirements for UI text.

## Photography

Photography should be one of the strongest brand assets.

Direction:

-   real food;
-   natural texture;
-   warm but truthful light;
-   close human perspective;
-   restrained styling;
-   no fake steam;
-   no misleading portion sizes;
-   consistent crop families for TV/web/social.

------------------------------------------------------------------------

# 18. Menu identity

Create templates for:

-   physical A4/A3 or chosen format;
-   takeaway menu;
-   table mini-menu;
-   daily menu;
-   digital web menu;
-   TV menu zone;
-   social daily-menu post.

The same design tokens and content source should drive all digital
versions.

Printed menus may be generated as export-ready PDFs later, but should
not be required for daily TV/site updates.

------------------------------------------------------------------------

# 19. Social identity

Create reusable templates:

-   Pratos do Dia
-   Prato em destaque
-   Takeaway
-   Conheça a Bulgária
-   Bastidores
-   Horário/aviso
-   Evento
-   Seasonal campaign

Content mix should not make the account a repetitive price board.

Recommended editorial pillars:

-   Food
-   Mariana / people
-   Bulgaria
-   Local community
-   Practical service information

------------------------------------------------------------------------

# 20. Roles

## Owner/Admin

Everything.

## Restaurant Manager

-   menu;
-   daily dishes;
-   promotions;
-   media;
-   cultural content;
-   customers/loyalty;
-   publication;
-   view device health.

No infrastructure secrets.

## Operator

-   today's dishes;
-   quick availability;
-   basic loyalty operation;
-   optionally promotions.

## Technical Support

-   device health;
-   deployment;
-   logs;
-   configuration.

No unnecessary customer-data access.

------------------------------------------------------------------------

# 21. Security and privacy

Implement:

-   HTTPS everywhere;
-   CSRF protection;
-   secure sessions;
-   rate limiting;
-   role-based authorization;
-   password hashing;
-   MFA for privileged admin if practical;
-   encrypted secrets;
-   no secrets in repository;
-   backups;
-   audit log for sensitive actions;
-   dependency scanning;
-   input validation;
-   upload MIME/size validation;
-   safe image processing;
-   least-privilege database credentials.

Customer data:

-   collect only what is needed;
-   explicit consent where required;
-   separate service-required data from marketing consent;
-   allow export/deletion workflows as legally required;
-   retention policy;
-   no loyalty/customer PII on the public signage endpoint.

GDPR/legal text must be reviewed for the actual business before
production.

------------------------------------------------------------------------

# 22. API outline

Example internal/public endpoints:

``` text
GET  /api/signage/v1/screens/{device}/manifest
POST /api/signage/v1/screens/{device}/heartbeat
POST /api/signage/v1/screens/{device}/telemetry

GET  /api/public/v1/today
GET  /api/public/v1/menu
GET  /api/public/v1/promotions
GET  /api/public/v1/stories

POST /api/loyalty/v1/identify
POST /api/loyalty/v1/transactions
GET  /api/loyalty/v1/me
```

Use signed/device authentication for signage endpoints that expose
device-specific information.

Public menu data can be cached aggressively.

------------------------------------------------------------------------

# 23. Signage manifest concept

Example:

``` json
{
  "schema_version": 1,
  "content_version": "2026-10-05T17:40:12Z",
  "valid_from": "2026-10-05T00:00:00+01:00",
  "layout": "restaurant-three-zone-v1",
  "today": {
    "title": "Hoje",
    "items": []
  },
  "slides": [],
  "promotions": [],
  "stories": [],
  "assets": [
    {
      "url": "https://example/assets/abc.webp",
      "sha256": "..."
    }
  ]
}
```

The Pi must not activate the manifest until every required asset has
successfully downloaded and validated.

------------------------------------------------------------------------

# 24. Observability

## Server

Track:

-   HTTP health;
-   queue health;
-   failed jobs;
-   storage;
-   DB connectivity;
-   social failures;
-   publication latency.

## Screen

Heartbeat payload may include:

-   device ID;
-   timestamp;
-   uptime;
-   player version;
-   content version;
-   last sync result;
-   free disk;
-   network state;
-   Chromium/service state.

Admin statuses:

-   Green: healthy
-   Amber: stale/degraded
-   Red: offline/error

Do not expose raw technical complexity to the restaurant operator unless
actionable.

------------------------------------------------------------------------

# 25. Backups

At minimum:

-   daily DB backup;
-   media/object-storage protection;
-   off-server backup;
-   retention policy;
-   periodic restore test.

A backup that has never been restored in testing is not considered
verified.

------------------------------------------------------------------------

# 26. Performance targets

Public site:

-   mobile-first;
-   strong Core Web Vitals target;
-   compressed responsive images;
-   lazy loading below fold;
-   minimal JavaScript.

TV:

-   initial last-known-good display after OS/player startup as quickly
    as practical;
-   no white/error page during network loss;
-   smooth 1080p operation;
-   content updates without full OS reboot;
-   no cumulative memory leak in multi-day operation.

Admin:

-   daily-menu form usable on a normal smartphone;
-   common publication operation should require only a few taps.

------------------------------------------------------------------------

# 27. Accessibility

Website/PWA:

-   semantic structure;
-   keyboard operation;
-   visible focus;
-   accessible forms;
-   WCAG AA colour contrast target;
-   reduced-motion support;
-   useful alt text.

TV:

-   large typography;
-   sufficient contrast;
-   viewing-distance testing;
-   avoid excessive animation.

------------------------------------------------------------------------

# 28. Test strategy

## Automated

-   unit tests;
-   feature tests;
-   permissions tests;
-   publication workflow tests;
-   API contract tests;
-   loyalty ledger tests;
-   social adapter tests with mocks;
-   signage manifest tests;
-   E2E tests for operator critical path.

## Mandatory physical tests

### Power-loss test

While signage is running:

1.  remove power without graceful shutdown;
2.  restore power;
3.  verify automatic boot;
4.  verify automatic kiosk;
5.  verify content appears without intervention;
6.  repeat multiple times.

### Internet-loss test

1.  display current content;
2.  disconnect WAN/Wi-Fi/Ethernet;
3.  verify display continues;
4.  leave offline;
5.  reconnect;
6.  verify sync;
7.  verify no manual action.

### Browser-crash test

Kill Chromium.

Expected: automatic recovery.

### Player-service crash test

Kill local service.

Expected: automatic recovery.

### Server-unavailable test

Central application unavailable.

Expected: last-known-good signage continues.

### Invalid-content test

Publish a manifest with missing/corrupt asset in staging test.

Expected: current version remains displayed.

### Long-run test

Run signage continuously for at least 72 hours before production
acceptance.

Watch:

-   RAM;
-   CPU;
-   browser stability;
-   disk;
-   transitions;
-   sync.

------------------------------------------------------------------------

# 29. Acceptance criteria --- MVP

The MVP is accepted only when all of the following are true:

-   operator can publish today's dishes from a phone;
-   published daily menu appears on website;
-   published daily menu appears on TV;
-   signage supports food imagery, Bulgaria content and promotions;
-   TV can advertise takeaway/services;
-   content can be scheduled;
-   Raspberry Pi starts unattended after power restoration;
-   kiosk starts automatically;
-   TV content survives Internet loss;
-   Chromium recovers automatically after crash;
-   OS watchdog is active and tested;
-   admin shows screen heartbeat/content freshness;
-   social publishing is either functional for configured eligible
    accounts or cleanly falls back without blocking core publication;
-   public site is responsive;
-   customer PWA is installable;
-   loyalty foundation is functional if included in the release
    milestone;
-   role permissions are tested;
-   backups are configured;
-   no critical/high security defects remain;
-   no staff action is required for normal signage operation.

------------------------------------------------------------------------

# 30. Implementation sequence for Codex

## Sprint 0 --- Repository and decisions

Codex must:

1.  initialise repository;
2.  create `README.md`;
3.  create `docs/ARCHITECTURE.md`;
4.  create `docs/BRAND.md`;
5.  create `docs/SIGNAGE.md`;
6.  create `docs/DEPLOYMENT.md`;
7.  create `docs/RASPBERRY_PI.md`;
8.  create `.env.example`;
9.  configure CI;
10. document every assumption.

**Gate 0:** architecture review before building broad functionality.

## Sprint 1 --- Domain + admin

Build migrations/models/tests for:

-   menu;
-   daily menu;
-   media;
-   promotions;
-   stories;
-   publications;
-   screens;
-   users/roles.

Build the mobile-first Filament operator flow.

**Gate 1:** user can create and publish a daily menu locally.

## Sprint 2 --- Website + signage renderer

Build:

-   public website;
-   `/today`;
-   menu;
-   stories;
-   takeaway;
-   contact;
-   `/screen/...` renderer;
-   1920×1080 signage layout.

**Gate 2:** one DB publication updates web + screen renderer.

## Sprint 3 --- Pi agent

Create `/device` or separate package containing:

-   provisioning;
-   local service;
-   manifest sync;
-   atomic cache;
-   Chromium launcher;
-   systemd units;
-   watchdog configuration;
-   logs;
-   health;
-   update mechanism;
-   recovery documentation.

**Gate 3:** physical power/network/crash tests pass.

## Sprint 4 --- Publishing pipeline

Implement:

-   channel abstraction;
-   social asset rendering;
-   queues;
-   retries;
-   status UI;
-   adapters;
-   manual fallback.

**Gate 4:** a failed external channel never blocks web/TV.

## Sprint 5 --- PWA + loyalty

Implement:

-   PWA manifest/service worker;
-   customer auth;
-   loyalty account;
-   secure QR;
-   ledger;
-   rewards;
-   customer history;
-   consent.

**Gate 5:** complete loyalty transaction lifecycle tested.

## Sprint 6 --- Production hardening

-   backups;
-   restore test;
-   monitoring;
-   security review;
-   rate limits;
-   permissions review;
-   Lighthouse/performance;
-   accessibility;
-   72-hour signage soak test;
-   deployment runbook.

**Gate 6:** production readiness sign-off.

------------------------------------------------------------------------

# 31. Codex engineering rules

Codex must follow these rules:

1.  **Do not invent business facts.**
2.  Do not add paid SaaS dependencies without explicit approval.
3.  Prefer boring, proven technology over unnecessary complexity.
4.  Keep signage functional independently of cloud availability.
5.  Never expose secrets to frontend code.
6.  Every critical workflow requires tests.
7.  Every external API must sit behind an interface/adapter.
8.  External API failure must degrade gracefully.
9.  No online ordering may be activated until restaurant acceptance
    workflow exists.
10. No static loyalty mechanism that can trivially self-award unlimited
    rewards.
11. Use database migrations; never depend on manual schema edits.
12. Seed development/demo data separately from production.
13. Keep UI copy in translation files.
14. Build PT-PT first but make localisation possible.
15. Store timestamps consistently and render in restaurant timezone.
16. Optimise signage assets before sending them to the Pi.
17. Validate checksums before activating cached signage versions.
18. Keep the previous known-good signage release.
19. Avoid dependencies on Smart TV operating systems.
20. Treat the Raspberry Pi as an appliance, not a desktop computer.
21. Document deployment and disaster recovery as code evolves.
22. Never declare a task complete solely because it works on a developer
    laptop.

------------------------------------------------------------------------

# 32. Initial repository structure

``` text
/
├── app/
├── bootstrap/
├── config/
├── database/
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
│       ├── public/
│       ├── pwa/
│       └── signage/
├── routes/
├── tests/
├── docs/
│   ├── ARCHITECTURE.md
│   ├── BRAND.md
│   ├── SIGNAGE.md
│   ├── SOCIAL.md
│   ├── SECURITY.md
│   ├── DEPLOYMENT.md
│   └── RASPBERRY_PI.md
├── device/
│   ├── agent/
│   ├── systemd/
│   ├── provisioning/
│   ├── scripts/
│   └── README.md
└── README.md
```

------------------------------------------------------------------------

# 33. Environment model

At least:

-   local
-   staging
-   production

Never test social publication against live public accounts by accident.

Use explicit environment flags and test destinations.

------------------------------------------------------------------------

# 34. Content and brand production still required

Engineering must not substitute placeholder AI assumptions for final
brand work.

Required creative deliverables:

-   final logo;
-   brand palette;
-   typography;
-   icon set;
-   TV master composition;
-   photography selection/direction;
-   menu templates;
-   social templates;
-   PWA icon;
-   favicon;
-   promotional copy;
-   Bulgaria editorial starter pack.

Until approved, use clearly marked development placeholders.

------------------------------------------------------------------------

# 35. Decisions intentionally deferred

These require owner/business confirmation, not engineering guesses:

-   final domain;
-   final telephone/email;
-   exact opening hours;
-   exact menu/prices;
-   whether loyalty is points, visits/stamps or hybrid;
-   reward economics;
-   whether WhatsApp is used;
-   exact Meta accounts and permissions;
-   future online payment provider;
-   whether future orders require prepayment;
-   final brand palette/logo;
-   exact Bulgarian cultural content and image rights.

None of these should block construction of the core architecture.

------------------------------------------------------------------------

# 36. Definition of success

The product succeeds when this daily scenario is boringly simple:

At 10:30, Mariana opens the admin on her phone, changes today's dishes
and taps **Publicar**.

Within a short period:

-   the restaurant TV displays the correct dishes;
-   the website displays the same information;
-   configured social channels receive the appropriate publication;
-   the TV continues displaying attractive food, Bulgarian storytelling
    and restaurant promotions;
-   customers can discover takeaway and the customer PWA;
-   no one touches the Raspberry Pi;
-   no one logs into the TV;
-   no content is entered twice.

If the Internet then fails, customers should not notice from the TV.

If power fails and returns, the signage should recover by itself.

That is the core product contract.

------------------------------------------------------------------------

# 37. First Codex instruction

Use this document as the authoritative product specification.

Before implementation:

1.  inspect the entire specification;
2.  create an implementation plan mapped to the sprint/gate structure;
3.  list only genuine blockers;
4.  identify security and operational risks;
5.  create the initial repository/documentation structure;
6.  implement Sprint 0;
7.  do **not** silently reduce offline/recovery requirements;
8.  do **not** substitute a third-party signage SaaS for the specified
    appliance architecture;
9.  do **not** implement unapproved paid services;
10. stop at Gate 0 with a concise architecture report before broad
    feature development.

The engineering priority order is:

**Reliability → operator simplicity → correctness → maintainability →
visual polish → optional features.**

------------------------------------------------------------------------

# 38. Implementation Completion Pack

## 38.1 Brand proposition

**O Cantinho da Mariana** is a warm neighbourhood restaurant with a
personal story and Bulgarian roots, expressed naturally in Portugal.

Internal brand territory:

> **Uma casa daqui. Com sabores que vieram de longe.**

Supporting campaign line:

> **Comida boa. Conversas melhores.**

The brand is warm, accessible, personal, contemporary, culturally
distinctive, confident and simple. It must not become folkloric,
visually noisy or a copy of a fast-food identity.

## 38.2 Staging visual tokens

These are implementation-ready staging values pending final owner
approval.

``` css
:root {
  --brand-burgundy-900: #5B1715;
  --brand-burgundy-700: #7A1E1E;
  --brand-green-800: #245B38;
  --brand-terracotta-600: #C46A3A;
  --brand-cream-100: #F7F0E4;
  --brand-beige-200: #E8D6B8;
  --brand-brown-800: #4B2E1F;
  --surface-page: #FFFDF9;
  --surface-card: #FFFFFF;
  --text-primary: #241A16;
  --text-muted: #6D625C;
  --border-soft: #E7DED5;
  --state-success: #287A45;
  --state-warning: #9B6817;
  --state-danger: #A72C2C;
}
```

Typography: use a warm, highly legible display serif plus a neutral UI
sans with complete Portuguese and Cyrillic support. Verify licensing and
Raspberry Pi rendering before production. Until the final vector logo is
approved, Codex must use a clearly provisional text lockup; an AI raster
concept must never be traced into a production logo.

## 38.3 Design system

Use an 8px spacing base: `4 / 8 / 12 / 16 / 24 / 32 / 48 / 64 / 96`.

Radius: small 8px, medium 12px, large 20px, pill 999px. Avoid excessive
rounded-card UI.

Minimum mobile touch target: 44×44 CSS px.

Required reusable components: BrandMark, PrimaryButton, SecondaryButton,
StatusBadge, DishCard, DailyMenuCard, Price, PromotionCard, StoryCard,
MediaPicker, PublishStatus, ChannelStatus, ScreenHealth, LoyaltyCard,
RewardProgress, PhoneCTA, QRCard, EmptyState, ErrorState and
OfflineState.

# 39. Signage Visual Specification

Reference canvas: **1920×1080 landscape**, scalable to 4K. Keep critical
information at least 48px equivalent from physical edges.

Normal layout states:

-   **Food + Story + Today:** approximately 34% / 32% / 34%.
-   **Promotion + Today:** approximately 66% / 34%.
-   **Food Feature + Today:** approximately 66% / 34%.
-   Exceptional full-screen campaigns are allowed only briefly and with
    frequency caps.

The right-hand `Hoje` panel is the persistent information anchor in
normal rotation.

Starting timing defaults:

-   food/story: 12--15 seconds;
-   promotion: 10--12 seconds;
-   transitions: approximately 500--800 ms;
-   no aggressive animation;
-   no audio by default.

`Hoje` hierarchy: service label, soup if relevant, main dishes, concise
accompaniment/inclusions, price, optional subtle CTA.

If no daily menu is published, show a branded fallback such as
**"Pergunte-nos pelos pratos de hoje"**. Never show blank fields,
browser errors or broken-image icons.

# 40. Admin UX

The mobile home screen prioritises the operational task, not analytics.

Above the fold:

-   greeting;
-   current daily-menu status;
-   large `Pratos de hoje` action;
-   last publication time;
-   actionable channel failures.

Default daily-menu editor:

-   soup;
-   dish 1;
-   dish 2;
-   price;
-   image;
-   publish.

Actions: `Usar os de ontem`, `Guardar rascunho`, `Pré-visualizar`,
`Publicar agora`, `Agendar`.

Preview tabs: TV, Site, Social.

Allow one-tap marking of a dish as unavailable. This updates live
channels without deleting history.

# 41. Public Website

Primary pages:

-   Hoje
-   Menu
-   O Cantinho
-   Bulgária à Mesa
-   Takeaway
-   Contactos

Homepage hierarchy:

1.  brand + strong food image;
2.  `Ver pratos de hoje`;
3.  `Ligar para encomendar`;
4.  today's menu;
5.  selected menu;
6.  takeaway;
7.  Mariana/restaurant story;
8.  Bulgaria content;
9.  location and hours;
10. loyalty/PWA CTA.

The digital menu must be native HTML, not PDF-only.

# 42. Customer PWA

Bottom navigation:

-   Hoje
-   Menu
-   Fidelização
-   Mais

Guest-first access. Account registration is required only for
customer-specific functions such as loyalty.

Provide web manifest, icons, theme metadata, service worker and sensible
offline shell. Do not force an App Store/Play Store installation.

# 43. Loyalty MVP

Recommended launch mechanic: **visits/stamps**, while retaining a
generic ledger architecture for future spend-based points.

The qualifying rule, reward and threshold remain business decisions.

The loyalty mechanism must prevent trivial self-awarding. Use
secure/rotating identifiers or an operator-controlled transaction flow,
not a reusable public static QR.

Every adjustment, earn, redemption and reversal is auditable.

# 44. Social Content Engine

Renderer templates:

-   daily-menu-portrait
-   daily-menu-story
-   dish-feature
-   takeaway
-   bulgaria-story
-   opening-hours
-   special-event

Canonical facts always come from the database. Generated caption
assistance may never invent dishes, prices, hours or claims.

Use idempotency keys to avoid duplicate social posts after retries.

# 45. Media Pipeline

On upload:

1.  validate file;
2.  preserve original;
3.  strip unsafe metadata where appropriate;
4.  generate responsive web derivatives;
5.  generate signage derivative;
6.  generate social crops;
7.  retain focal-point metadata.

Derivatives must always be generated from the original, never repeatedly
recompressed from another derivative.

# 46. Raspberry Pi Device Package

Suggested repository structure:

``` text
/device
  /agent
    app/
    tests/
  /config
  /systemd
    cantinho-agent.service
    cantinho-kiosk.service
  /scripts
    install.sh
    configure.sh
    healthcheck.sh
    rollback.sh
  /kiosk
    chromium-flags.conf
  /docs
```

Service model:

``` text
network-online.target (soft dependency)
        |
        +-- cantinho-agent
        |       +-- local HTTP server + sync
        |
        +-- graphical session
                +-- cantinho-kiosk
                        +-- Chromium -> localhost
```

The kiosk must start even when WAN access is unavailable.

Local endpoints may include `GET /`, `GET /health`, and `GET /version`,
bound to localhost unless explicitly required otherwise.

# 47. Device Provisioning Checklist

For each Raspberry Pi:

1.  flash supported 64-bit Raspberry Pi OS;
2.  assign unique hostname/device ID;
3.  patch OS during provisioning;
4.  create restricted kiosk/support users;
5.  install agent dependencies;
6.  install Chromium;
7.  install systemd units;
8.  install approved fonts/assets;
9.  register device with central server;
10. provision unique device credentials;
11. configure local versioned cache;
12. configure hardware/kernel watchdog;
13. configure filesystem resilience;
14. configure log rotation;
15. configure secure remote support;
16. configure kiosk autostart;
17. reboot;
18. test without Internet;
19. test abrupt power cycle repeatedly;
20. record asset/device information.

# 48. Production Deployment

Initial scale deliberately avoids unnecessary infrastructure.

``` text
Internet
   |
Nginx
   |
Laravel
   |---- MySQL
   |---- Redis (recommended)
   |---- Queue worker
   |---- Scheduler
   |---- Object/media storage
```

A properly managed VPS may initially host the application, database and
Redis. Backups must be off-server and restore-tested.

Do **not** introduce Kubernetes for this scale.

# 49. CI/CD

Pipeline:

1.  install dependencies;
2.  lint/static analysis;
3.  unit/feature tests;
4.  frontend build;
5.  dependency/security checks;
6.  deploy staging;
7.  smoke tests;
8.  manual production approval;
9.  production deploy;
10. controlled migrations;
11. health verification.

Pi software has a separate versioned release pipeline with validation
and rollback.

# 50. Initial Database Set

``` text
users
roles / permissions
restaurants
menu_categories
menu_items
daily_menus
daily_menu_items
media_assets
promotions
stories
publications
publication_attempts
screens
screen_heartbeats
customers
customer_consents
loyalty_accounts
loyalty_transactions
rewards
reward_redemptions
audit_logs
```

Future:

``` text
orders
order_items
order_status_events
pickup_slots
payments
```

# 51. Background Jobs

``` text
PublishContent
RenderSocialAsset
PublishFacebook
PublishInstagram
ExpireDailyMenu
ExpirePromotion
GenerateMediaDerivatives
EvaluateScreenHealth
SendOperationalAlert
ProcessLoyaltyExpiry
```

Jobs should be idempotent wherever possible.

# 52. Operational Alerts

Technical support alerts:

-   screen offline beyond threshold;
-   stale screen content;
-   repeated social publication failure;
-   queue failure;
-   backup failure;
-   low disk;
-   application health failure.

Restaurant staff see only actionable business-facing messages.

# 53. Launch Content Pack

Before opening, prepare at least:

-   15--20 strong food photographs;
-   8--12 Bulgaria story cards;
-   4 takeaway/service promotions;
-   3 generic brand fallback slides;
-   opening-hours card;
-   contact card;
-   complete initial menu;
-   first week of social templates.

# 54. Physical Brand Touchpoints

Apply the system consistently to:

-   exterior sign;
-   opening-hours sign;
-   printed menu;
-   table QR;
-   loyalty QR;
-   takeaway bag/sticker;
-   optional staff clothing;
-   receipts/footer;
-   social profiles;
-   TV;
-   website/PWA.

QR codes always need a human-readable explanation.

# 55. Launch Plan

**T-21 to T-14:** identity approval, business facts, menu structure,
photography, hosting/repository, core domain/admin.

**T-14 to T-7:** website/signage, Pi provisioning, content ingestion,
social integration, publication workflow.

**T-7 to T-3:** physical TV/Pi installation, readability test,
power/network failure tests, operator training, loyalty pilot if
included.

**T-3 to T-1:** 72-hour soak test, content proofing, backup/restore
verification, final credentials, social publication test.

**Launch:** verify today's menu, TV, website, phone CTA and monitoring.

**T+1 / T+7:** review incidents and usage, tune signage timing, simplify
operator friction.

# 56. Operator Training

Training target: **20--30 minutes**.

Teach only:

1.  login;
2.  change today's dishes;
3.  publish;
4.  mark unavailable;
5.  add/change photo;
6.  activate/deactivate promotion;
7.  recognise success/error;
8.  know whom to contact if the display is physically off.

Never train restaurant staff to administer Linux or Raspberry Pi.

# 57. Definition of Done

**Brand:** approved vector identity, tokens, typography and templates.

**Admin:** a non-technical operator completes the daily workflow
unaided.

**Website:** production content, mobile UX, SEO basics, accessibility
and performance accepted.

**Signage:** physical failure tests and 72-hour soak pass.

**Social:** supported channels publish reliably and fallback is usable.

**Loyalty:** earn/redeem/reverse/audit lifecycle is secure.

**Operations:** backup, monitoring, recovery and support documentation
are verified.

# 58. Final Codex Build Directive

Proceed in gates. Do not wait for final artwork to begin
architecture/domain work, but do not invent a permanent brand.

After Gate 0, continue through approved gates without asking for trivial
implementation decisions already resolved by this specification or
standard engineering practice.

Escalate only decisions that materially affect:

-   business rules;
-   cost;
-   security/privacy;
-   external account permissions;
-   irreversible architecture;
-   public brand identity.

For normal implementation details, choose the simplest maintainable
solution, document it and continue.

The product must remain usable when Meta is unavailable, restaurant
Internet is unavailable, the central server is temporarily unavailable,
Chromium crashes, or power is interrupted.

The remaining unavoidable restaurant-side failure class should be
physical hardware/power/display failure beyond software recovery.
