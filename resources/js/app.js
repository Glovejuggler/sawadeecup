import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import Authenticated from './Layouts/AuthenticatedLayout.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: async (name) => {
        const page = await resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue'))

        if (page.default.layout === undefined) {
            page.default.layout = Authenticated
        }

        return page
    },
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#cccccc',
    },
});

// Custom methods
Date.prototype.toWordFormat = function () {
    return Intl.DateTimeFormat('en-US', {month: 'long', day: 'numeric', year: 'numeric'}).format(this)
}

Date.prototype.toTimeFormat = function () {
    return Intl.DateTimeFormat('en-US', {hour: 'numeric', minute: '2-digit', hour12: true}).format(this)
}

Number.prototype.amountFormat = function () {
    return this > 0 ? this.toLocaleString(undefined, {minimumFractionDigits: 2,maximumFractionDigits: 2}) : 0
}

Number.prototype.pad = function(size) {
    var s = String(this);
    while (s.length < (size || 2)) {s = "0" + s;}
    return s;
}