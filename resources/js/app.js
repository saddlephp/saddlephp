import '../css/panel.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';

const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });

createInertiaApp({
    resolve: (name) => pages[`./Pages/${name}.vue`],
    setup({ el, App, props, plugin }) {
        warnOnStaleAssets(props.initialPage?.props?.saddle?.version);

        createApp({ render: () => h(App, props) }).use(plugin).mount(el);
    },
    progress: { color: '#d9501f', showSpinner: false },
});

// Published assets can fall behind the installed composer package; nudge
// the developer toward `php artisan saddle:upgrade` when versions diverge.
function warnOnStaleAssets(serverVersion) {
    if (typeof __SADDLE_VERSION__ === 'undefined' || !serverVersion) return;

    if (serverVersion !== __SADDLE_VERSION__) {
        console.warn(
            `[Saddle] Published panel assets are v${__SADDLE_VERSION__} but the installed package is v${serverVersion}. Run: php artisan saddle:upgrade`,
        );
    }
}
