import './bootstrap';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/inertia-vue3';

// Use import.meta.glob to allow resolving pages placed in subfolders (e.g. Pages/Orders/Index.vue)
const pages = import.meta.glob('./Pages/**/*.vue');

createInertiaApp({
  resolve: name => {
    const importPath = `./Pages/${name}.vue`;
    const loader = pages[importPath];
    if (!loader) {
      throw new Error(`Unknown page: ${importPath}`);
    }
    return loader();
  },
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .mount(el);
  },
});