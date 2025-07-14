import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

import 'vue-cal/dist/vuecal.css'

import Toast from 'vue-toastification';
import 'vue-toastification/dist/index.css';

import { createPinia } from 'pinia'; // ✅ Import Pinia

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) => {
    const pages = import.meta.glob('./Pages/**/*.vue');
    return pages[`./Pages/${name}.vue`]().then((module) => {
      const page = module.default;
      // Support both defineOptions and classic export default
      if (module.layout) page.layout = module.layout;
      if (typeof page.layout === 'undefined') {
        page.layout = (h, page) => h('div', page);
      }
      return page;
    });
  },
  setup({ el, App, props, plugin }) {
    const app = createApp({
      render: () => h(App, props),
    });

    const pinia = createPinia();         // ✅ Create Pinia instance

    app
      .use(plugin)
      .use(ZiggyVue)
      .use(pinia)                         // ✅ Inject Pinia
      .use(Toast, {
        position: 'top-right',
        timeout: 3000,
      })
      .mount(el);

    return app;
  },
  progress: {
    color: '#4B5563',
  },
});

