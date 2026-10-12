# Gate 4 — Product UX shell

## Implemented
- First-party login/logout flow without depending on a generated auth scaffold.
- Mobile-first operator dashboard.
- Today's menu card, channel publication states and screen health.
- Draft editor with TV/site previews.
- Public Today experience and installable PWA shell.
- Service-worker cache for the public shell.
- Visual tokens consolidated into one lightweight CSS file.

## Deliberately not invented
Phone, opening hours, permanent menu, real photography, final logo/vector assets,
loyalty reward rules and Meta credentials remain configurable/empty.

## Acceptance intent
A restaurant operator should reach the daily menu editor in one tap, understand
whether content is public, preview TV/site, and see whether the restaurant screen
is alive without seeing technical infrastructure terminology.

## Remaining before this gate is production-verified
Laravel dependencies must be installed and the application exercised in a real
runtime/browser. Visual QA must be repeated with final content and brand assets.
