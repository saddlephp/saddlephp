A plugin is a regular Composer package. Its service provider registers resources, scripts, and stylesheets through the `Saddle` facade, and Laravel's package auto-discovery boots it alongside your application with no manual wiring.

### Plugin anatomy

```php
// In your plugin's service provider:

public function boot(): void
{
    Saddle::register([MoodBoardResource::class]);
    Saddle::script('/vendor/mood-board/field.js');
    Saddle::style('/vendor/mood-board/field.css');
}
```

- `Saddle::register(array)` adds resource classes to the panel's resource list. Plugin resources are indistinguishable from application resources once registered.
- `Saddle::script(string)` queues a script URL to be loaded on every panel page after the core bundle.
- `Saddle::style(string)` queues a stylesheet URL in the same way.

### Extending the panel shell

Three hooks let a plugin extend the panel itself. Call them from your service provider's `boot()`:

```php
use Illuminate\Http\Request;

// Contribute extra data to the shared `saddle` Inertia prop (read it from your
// plugin's own frontend). Core keys always win, so a plugin cannot clobber the
// documented shape.
Saddle::sharing(fn (Request $request) => [
    'moodBoard' => ['recentColors' => MoodColor::recent()],
]);

// Transform the sidebar navigation: reorder or filter groups, or append custom
// links. The callback receives the computed nav array and the request.
Saddle::navUsing(fn (array $nav, Request $request) => [...$nav, [
    'group' => 'Plugins',
    'items' => [['label' => 'Mood Docs', 'uriKey' => 'mood-docs', 'icon' => null, 'active' => false]],
]]);

// Allow extra theme tokens so your styles or custom elements can read them as
// CSS custom properties (injected as `--color-<token>` when set in
// `saddle.brand.theme`). Token names must be lowercase CSS identifiers.
Saddle::registerThemeTokens('mood-accent');
```

### Publishing assets

Compile your frontend assets (custom elements, styles) and publish them to `public/vendor/{plugin}/` using Laravel's standard `publishes` mechanism in your service provider:

```php
public function boot(): void
{
    $this->publishes([
        __DIR__.'/../dist/field.js'  => public_path('vendor/mood-board/field.js'),
        __DIR__.'/../dist/field.css' => public_path('vendor/mood-board/field.css'),
    ]);

    Saddle::script('/vendor/mood-board/field.js');
    Saddle::style('/vendor/mood-board/field.css');
}
```

Users install the assets with `php artisan vendor:publish --tag=mood-board-assets`.

### Custom fields and columns

Plugins can ship their own field and column renderers as custom elements. On the PHP side:

```php
CustomField::make('mood')->tag('mood-picker')->rules('max:32'),
CustomColumn::make('mood')->tag('mood-cell'),
```

The panel fulfils the following contract:

- **CustomField:** sets the element's `value` and `field` DOM properties; listens for a `saddle:input` CustomEvent whose `detail` is the new value.
- **CustomColumn:** sets `value` and `column` DOM properties (read-only; no input event expected).

### Custom element implementation

A complete vanilla custom element that correctly handles the field contract, including early value assignment (the panel may set the `value` property before the element connects to the DOM):

```js
class MoodPicker extends HTMLElement {
    connectedCallback() {
        // The panel may set the value property before the element is
        // connected, so seed the input from whatever arrived early.
        this._input = document.createElement('input');
        this._input.value = this._value ?? '';
        this._input.addEventListener('input', () => {
            this.dispatchEvent(new CustomEvent('saddle:input', {
                bubbles: true,
                detail: this._input.value.toUpperCase(),
            }));
        });
        this.appendChild(this._input);
    }

    set value(v) {
        this._value = v ?? '';
        if (this._input) this._input.value = this._value;
    }

    get value() { return this._input ? this._input.value : (this._value ?? ''); }
}

customElements.define('mood-picker', MoodPicker);
```

Define elements at the top level of your script. The browser upgrades any matching elements the panel has already rendered as soon as `customElements.define` runs, so load order never matters.

### Framework-agnostic

The contract is framework-agnostic. Anything that compiles to a standard custom element works: Vue's `defineCustomElement`, Lit, React wrappers, Svelte custom elements. Plugin authors are not tied to the panel's internals.
