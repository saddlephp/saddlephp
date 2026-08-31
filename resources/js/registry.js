/**
 * Runtime registry for plugin-supplied Vue components.
 *
 * The panel's dispatch tables (FormRenderer's fields and layouts,
 * WidgetRenderer's widgets) are built-in maps. This registry is consulted
 * BEFORE them, so a plugin can add a component under a new key, or override a
 * built-in one, without the maps having to know about it.
 *
 * Plugin scripts are loaded on every panel page via `Saddle::script()`, and
 * they run after the panel bundle's top-level code but before Vue renders, so
 * registering at script top level is enough:
 *
 *     window.Saddle.registerField('color-picker-field', ColorPicker);
 *
 * Lookups happen at render time rather than module-evaluation time, so
 * registration order does not matter as long as it happens before first paint.
 */
const registries = {
    field: Object.create(null),
    layout: Object.create(null),
    widget: Object.create(null),
};

function register(kind, name, component) {
    if (typeof name !== 'string' || !name || !component) return;
    registries[kind][name] = component;
}

function resolve(kind, name, builtins) {
    return registries[kind][name] ?? builtins[name];
}

export const registerField = (name, component) => register('field', name, component);
export const registerLayout = (name, component) => register('layout', name, component);
export const registerWidget = (name, component) => register('widget', name, component);

export const resolveField = (name, builtins) => resolve('field', name, builtins);
export const resolveLayout = (name, builtins) => resolve('layout', name, builtins);
export const resolveWidget = (name, builtins) => resolve('widget', name, builtins);

/** Expose the registration half on the boot surface for plugin scripts. */
export function exposeRegistry(target) {
    target.Saddle = Object.assign(target.Saddle ?? {}, {
        registerField,
        registerLayout,
        registerWidget,
    });
}
