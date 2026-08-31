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
// `saddle.brand.theme`). Token names must start with a letter and contain only
// lowercase letters, digits, and hyphens (a-z, 0-9, -); other names are ignored.
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

### Registering Vue components

The panel is a Vue app, so the most direct way to ship a renderer is a Vue SFC. Register it against a component key and the panel's dispatch tables will use it:

```js
import ColorPicker from './ColorPicker.vue';

window.Saddle.registerField('color-picker-field', ColorPicker);
window.Saddle.registerLayout('accordion', AccordionLayout);
window.Saddle.registerWidget('gauge-widget', GaugeWidget);
```

Plugin scripts registered with `Saddle::script()` load on every panel page and run before the app mounts, so registering at script top level is enough.

The registry is consulted **before** the built-in maps, so you can add a new key or override a shipped one.

On the PHP side, point a field at your key:

```php
Text::make('brand_color')->component('color-picker-field'),
```

`Field::component()` reads the key with no argument and sets it with one. `Widget::component()` reads a widget's key, which is what `registerWidget()` matches on — so `Widget` is now genuinely open to subclassing beyond `StatWidget` and `ChartWidget`.

A registered field component receives the same props as a built-in one: `field` (the serialized field) and `modelValue`, and it should emit `update:modelValue`.

### Custom fields and columns

If you would rather not ship Vue — for a renderer you want to reuse outside the panel, or to avoid a build step — plugins can ship field and column renderers as custom elements instead. On the PHP side:

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
