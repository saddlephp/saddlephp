The panel is built on CSS custom-property design tokens, so theming is a matter of overriding a handful of variables. It ships with light and dark palettes and a per-user theme toggle.

### Dark mode

A **Light / Dark / System** toggle sits in the sidebar. The choice persists in the browser (`localStorage`) and is applied by a tiny inline script before the page paints, so there is no flash of the wrong theme. Dark mode adds a `dark` class to the `<html>` element, which swaps the dark palette in. No configuration is required.

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

Available tokens: `bg`, `surface`, `surface-2`, `ink`, `ink-2`, `ink-3`, `line`, `line-2`, `accent`. Unknown keys and malformed colors are ignored.

### Custom CSS

The tokens are plain CSS variables (`--color-bg`, `--color-ink`, `--color-accent`, …), so you can also override them from your own stylesheet, including under `.dark` for a custom dark palette:

```css
.dark {
    --color-bg: #0b1020;
    --color-accent: #60a5fa;
}
```
