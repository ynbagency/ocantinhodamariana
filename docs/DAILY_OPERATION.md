# Daily operator workflow

## Prepare and publish

Open `Operação → Pratos de hoje`. When the draft is empty, `Usar os de ontem` copies yesterday's latest published menu and price. Every copied dish starts available; review today's availability. Existing draft items are never overwritten by this action.

`Guardar rascunho` changes only the working copy. `Publicar agora` saves the visible form fields and publishes them in the same database transaction. At least one available item is required. Previews show the last saved draft.

## Sold-out dishes

In `Menu publicado`, use `Esgotou` or `Voltar a disponibilizar`. This creates a new immutable publication and keeps prior history. A separate draft or scheduled draft is preserved. A stale page cannot overwrite a newer publication; refresh when the version warning appears.

When every published dish is unavailable, the site and TV show a helpful message without an orphaned price. The operator can restore a dish using the same controls.

## Schedule today's saved draft

Save the draft, choose a future time today and press `Agendar rascunho guardado`. Times use the application's `Europe/Lisbon` timezone. The previous public snapshot remains active until the schedule runs.

Saving another draft cancels the schedule; schedule the updated draft again if desired. Publishing immediately also clears it. `Cancelar agendamento` cancels explicitly. Changing availability on the published version preserves the schedule.

The Laravel scheduler must run continuously:

```bash
php artisan schedule:work
```

Production should enable the provided `ops/systemd/cantinho-scheduler.service`. The due-menu command runs every minute and locks each menu to prevent duplicate publication. If the server recovers after the service day has ended, it cancels the expired schedule and records an operational warning instead of publishing yesterday's dishes.

Future-day preparation/scheduling is not exposed in this iteration. Social publication remains pending until its adapter is implemented and configured.

## Validation

Run `php vendor/phpunit/phpunit/phpunit`. Tests cover copy protection, current form publication, availability and stale versions, scheduled execution once, cancellation on editing, expired schedules and explicit draft availability.
