import '../css/app.css';
import './bootstrap';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { ZiggyVue } from 'ziggy-js';

createInertiaApp({
  title: (title) => (title ? `${title} · TDB` : 'To-Do BEM'),
  resolve: (name) => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
    return pages[`./Pages/${name}.vue`];
  },
  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(ZiggyVue);

    app.mount(el);
    requestAnimationFrame(() => requestAnimationFrame(runCountUp));
    return app;
  },
  progress: { color: '#2383E2', showSpinner: false },
});

// Animated count-up for [data-countup] number elements (e.g. dashboard stats).
function runCountUp() {
  if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  document.querySelectorAll('[data-countup]:not([data-cu-done])').forEach((el) => {
    const target = parseInt(String(el.textContent).replace(/[^\d-]/g, ''), 10);
    if (!Number.isFinite(target)) return;
    el.dataset.cuDone = '1';
    const duration = 650,
      start = performance.now();
    const tick = (now) => {
      const p = Math.min(1, (now - start) / duration);
      const eased = 1 - Math.pow(1 - p, 3);
      el.textContent = String(Math.round(target * eased));
      if (p < 1) requestAnimationFrame(tick);
      else el.textContent = String(target);
    };
    requestAnimationFrame(tick);
  });
}
document.addEventListener('inertia:navigate', () => requestAnimationFrame(() => requestAnimationFrame(runCountUp)));
