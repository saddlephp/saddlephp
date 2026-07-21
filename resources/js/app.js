import '../css/panel.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { createI18n } from 'vue-i18n';

const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });

// Populated from the shared props on boot so the document title always carries
// the brand name. Without a title callback the `<title inertia>` element is
// emptied on client-side navigation and the browser tab falls back to the URL.
let appName = 'Saddle';

createInertiaApp({
    resolve: (name) => pages[`./Pages/${name}.vue`],
    title: (title) => (title ? `${title} · ${appName}` : appName),
    setup({ el, App, props, plugin }) {
        const saddle = props.initialPage.props.saddle;
        appName = saddle.name ?? appName;
        const i18n = createI18n({
            legacy: false,
            locale: saddle.locale,
            fallbackLocale: saddle.locale,
            missingWarn: false,
            fallbackWarn: false,
            messages: { [saddle.locale]: saddle.translations },
        });

        createApp({ render: () => h(App, props) }).use(plugin).use(i18n).mount(el);
    },
    progress: { color: '#d9501f', showSpinner: false },
});
