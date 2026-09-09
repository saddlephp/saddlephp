The panel is built on CSS custom-property design tokens, so theming is a matter of overriding a handful of variables. It ships with light and dark palettes and a per-user theme toggle.

### Dark mode

A **Light / Dark / System** toggle sits in the sidebar. The choice persists in the browser (`localStorage`) and is applied by a tiny inline script before the page paints, so there is no flash of the wrong theme. Dark mode adds a `dark` class to the `<html>` element, which swaps the dark palette in. No configuration is required.

### Content-Security-Policy

That pre-paint script is the panel shell's only inline `<script>`, and a strict
CSP refuses it: every panel page logs a violation and the theme class never
applies before paint, so dark-mode users see a flash of the light theme on every
navigation.

Saddle cannot guess how your application produces a nonce, so hand it a callback
from a service provider. It is invoked once per shell render, so a per-request
nonce stays per-request:

```php
use SaddlePHP\Facades\Saddle;

public function boot(): void
{
    Saddle::resolveNonceUsing(fn () => app('csp.nonce'));

    // Or, with Laravel's Vite nonce:
    // Saddle::resolveNonceUsing(fn () => Vite::cspNonce());
}
```

Register nothing and the shell renders exactly as it always has. If the callback
throws, or returns anything other than a non-empty string, the attribute is
omitted rather than the page failing — a misconfigured nonce provider costs you
the pre-paint theme class, not the panel.

### Theme tokens

Override panel colors with the `saddle.brand.theme` config map. Keys are token names; values are validated CSS colors (a bare hex, or a single `rgb()`/`hsl()`/`oklch()`).

```php
// config/saddle.php
'brand' => [
    'name' => 'Acme',
    'accent' => '#2563eb',
    'theme' => [
        'ink' => '#0f172a',
        'surface' => '#f8fafc',
    ],
],
```

Available tokens: `bg`, `surface`, `surface-2`, `ink`, `ink-2`, `ink-3`, `line`, `line-2`, `accent`. Unknown keys and malformed colors are ignored. Plugins can add tokens to this allowlist with `Saddle::registerThemeTokens(...)` (see the plugins guide).

### Custom CSS

The tokens are plain CSS variables (`--color-bg`, `--color-ink`, `--color-accent`, …), so you can also override them from your own stylesheet, including under `.dark` for a custom dark palette:

```css
.dark {
    --color-bg: #0b1020;
    --color-accent: #60a5fa;
}
```
