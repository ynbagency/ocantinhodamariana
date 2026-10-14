# Backoffice access and verification

## Create the first account

Run on the application server:

```bash
php artisan cantinho:create-user owner@example.com --name="Owner name" --admin
```

The command asks for a password twice without echoing it. Passwords must have at least 12 characters. Existing accounts are never overwritten. To create an operator, omit `--admin`.

For local setup, `--generate` creates a random 32-character password and writes the login details under the private local storage disk, in `bootstrap/login-<email-hash>.txt`. The password is never printed by the command and the file is excluded from Git. Open `/entrar` to log in.

The current development database has a provisional administrator `admin@cantinho.local`. It is a local technical account, not an approved restaurant identity or a production bootstrap account. Its credential file is private and ignored.

## Operator navigation

All backoffice pages share navigation and logout: Início, Hoje, Conteúdos, Fotografias, Clientes and Definições. The active destination is marked. On mobile, navigation has two rows and the page has clearance below its content. A skip link and visible keyboard focus are provided.

The dashboard prioritises today's published menu and its edit action. It also shows pending draft/scheduled publication, last publication time, TV contact and publication destinations. Operator alerts use fixed actionable wording; raw error messages, contexts and tokens are not rendered. Recovered TV contact resolves its historical offline alert.

## Automated browser verification

```bash
npm ci --prefix tests/browser
cd tests/browser
npx playwright install --with-deps chromium
cd ../..
node tests/browser/mobile.cjs
```

On Windows the test uses installed Chrome by default. Override `BROWSER_EXECUTABLE` to use another installed Chromium binary. Linux uses Playwright Chromium.

The test creates a separate SQLite database and test-only account, starts an isolated server on port 8011, checks login/navigation/publication/sold-out/logout, and closes the server/browser. It never edits the normal local database. It checks 390×844 and overflow at 320, 768 and 1280 pixels. Screenshots are saved under `storage/framework/testing/browser`, excluded from Git.

This verifies browser behaviour at mobile viewport sizes. A physical smartphone usability review remains useful before launch.
