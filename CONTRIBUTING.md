# Contributing to Saddle

Thanks for riding along. Bug reports, docs fixes and pull requests are all welcome.

## Local development

```bash
composer install
npm install
npm run build
vendor/bin/pest
```

Before opening a pull request, also run `vendor/bin/pint --test` and `composer analyse` (PHPStan); CI runs both.

The `workbench/` directory contains a minimal host application used by the test suite and for manual poking. It registers `HorseResource`, `RiderResource` and `RanchResource` along with a couple of dashboard widgets.

## Browsing the demo panel

`DemoSeeder` fills the workbench with a fixed set of ranches, riders and horses, and `SADDLE_DEMO=1` turns on a `/demo-login` route that signs you in as its user (the panel sits behind the `auth` middleware, and the workbench has no real login page).

```bash
export SADDLE_DEMO=1 DB_CONNECTION=sqlite DB_DATABASE="$PWD/workbench/demo.sqlite"
touch workbench/demo.sqlite
vendor/bin/testbench migrate:fresh --seeder='Workbench\Database\Seeders\DemoSeeder'
vendor/bin/testbench vendor:publish --tag=saddle-assets --force
vendor/bin/testbench serve --port=8765
```

Then open `http://127.0.0.1:8765/demo-login`.

## README screenshots

The screenshots in the README are captured from that demo panel, in light and dark, by `.github/screenshots/capture.mjs`. With the demo server running:

```bash
npx playwright install chromium   # once
node .github/screenshots/capture.mjs
```

It rewrites `.github/screenshots/{dashboard,table,form}-{light,dark}.png`. Re-run it whenever the panel's look changes.

## Translations

The README is translated into 13 languages under `docs/translations/`, and the panel's own strings live in `lang/{locale}/panel.php`. `tests/Unit/LanguageParityTest.php` checks that every locale translates the full English key set.
