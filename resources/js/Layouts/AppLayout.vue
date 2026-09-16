<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import TdbIcon from '@/Components/UI/TdbIcon.vue';
import TdbDropdown from '@/Components/UI/TdbDropdown.vue';

const props = defineProps({ title: { type: String, required: true } });
const page = usePage();
const collapsed = ref(localStorage.getItem('tdb-sidebar') === 'collapsed');
const mobileOpen = ref(false);
const query = ref('');
const toast = ref(null);
let toastTimer;

const groups = [
  { label: 'Overview', items: [{ label: 'Dashboard', route: 'dashboard', icon: 'dashboard' }] },
  {
    label: 'Workspace',
    items: [
      { label: 'Projects', route: 'projects.index', icon: 'projects' },
      { label: 'Tasks', route: 'tasks.index', icon: 'tasks' },
      { label: 'My Tasks', route: 'my-tasks', icon: 'tasks' },
      { label: 'Calendar', route: 'calendar', icon: 'calendar' },
      { label: 'Files', route: 'files', icon: 'paperclip' },
    ],
  },
  { label: 'Collaborate', items: [{ label: 'Rooms', route: 'rooms.index', icon: 'rooms' }] },
  { label: 'Insights', items: [{ label: 'Reports', route: 'reports', icon: 'reports' }] },
  { label: 'System', items: [{ label: 'Settings', route: 'settings.edit', icon: 'settings' }] },
];
const isActive = (name) =>
  route().current(name) ||
  (name === 'projects.index' && route().current('projects.*')) ||
  (name === 'rooms.index' && route().current('rooms.*'));
const submitSearch = () => {
  if (query.value.trim()) router.get(route('search'), { q: query.value.trim() });
};
const saveCollapse = () => {
  collapsed.value = !collapsed.value;
  localStorage.setItem('tdb-sidebar', collapsed.value ? 'collapsed' : 'open');
};
const preferredAppearance = computed(
  () => page.props.auth?.user?.appearance ?? localStorage.getItem('tdb-appearance') ?? 'system',
);
const applyAppearance = (choice) => {
  localStorage.setItem('tdb-appearance', choice);
  const dark = choice === 'dark' || (choice === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
  document.documentElement.classList.toggle('dark', dark);
};
const setAppearance = (choice) => {
  if (page.props.auth?.user) {
    router.patch(route('settings.appearance'), { appearance: choice }, { preserveScroll: true, preserveState: true });
  } else {
    applyAppearance(choice);
  }
};
const showFlash = () => {
  const success = page.props.flash?.success;
  const error = page.props.flash?.error;
  if (!success && !error) return;
  toast.value = { type: error ? 'error' : 'success', message: error || success };
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => (toast.value = null), 4500);
};
watch(() => [page.props.flash?.success, page.props.flash?.error], showFlash);
watch(preferredAppearance, applyAppearance);
onMounted(() => {
  applyAppearance(preferredAppearance.value);
  showFlash();
});
onBeforeUnmount(() => clearTimeout(toastTimer));
</script>

<template>
  <Head :title="title" />
  <div class="layout-shell">
    <Transition name="fade"
      ><button v-if="mobileOpen" class="mobile-overlay" aria-label="Close navigation" @click="mobileOpen = false"
    /></Transition>
    <aside class="sidebar" :class="{ collapsed, 'mobile-open': mobileOpen }" aria-label="Main navigation">
      <div class="sidebar-brand">
        <Link :href="route('dashboard')" class="flex min-w-0 items-center gap-3" @click="mobileOpen = false">
          <img src="/logo.png" alt="To-Do BEM Logo" class="h-8 w-auto flex-none" />
          <span class="brand-copy min-w-0">
            <strong class="tdb-heading block truncate text-lg">To-Do BEM</strong>
          </span>
        </Link>
      </div>
      <nav class="nav-scroll">
        <template v-for="group in groups" :key="group.label">
          <p class="nav-group-label">{{ group.label }}</p>
          <Link
            v-for="item in group.items"
            :key="item.route"
            :href="route(item.route)"
            class="nav-item"
            :class="{ active: isActive(item.route) }"
            :aria-current="isActive(item.route) ? 'page' : undefined"
            @click="mobileOpen = false"
          >
            <TdbIcon :name="item.icon" :size="21" /><span class="nav-copy">{{ item.label }}</span>
          </Link>
        </template>
      </nav>
      <div class="sidebar-footer">
        <div class="flex items-center gap-2 px-2 py-1">
          <span
            class="grid h-9 w-9 flex-none place-items-center rounded-[10px_8px_11px_7px] bg-[var(--surface-2)] text-sm font-bold"
            >{{ page.props.auth.user.name.charAt(0).toUpperCase() }}</span
          >
          <div class="nav-copy min-w-0 flex-1">
            <strong class="block truncate text-sm">{{ page.props.auth.user.name }}</strong
            ><span class="block truncate text-xs text-[var(--muted)]">{{ page.props.auth.user.email }}</span>
          </div>
          <Link
            :href="route('logout')"
            method="post"
            as="button"
            class="nav-copy text-xs font-bold text-[var(--muted)] hover:text-[var(--danger)]"
            >Log out</Link
          >
        </div>
      </div>
    </aside>

    <div class="main-wrap" :class="{ collapsed }">
      <header class="topbar">
        <button
          class="tdb-btn tdb-btn-ghost !min-h-10 !px-2"
          :aria-label="mobileOpen ? 'Close navigation' : 'Open navigation'"
          @click="mobileOpen = !mobileOpen"
        >
          <TdbIcon name="menu" :size="22" />
        </button>
        <button
          class="tdb-btn tdb-btn-ghost hide-mobile !min-h-10 !px-2"
          :aria-label="collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
          @click="saveCollapse"
        >
          <TdbIcon :name="collapsed ? 'chevron-right' : 'chevron-left'" :size="20" />
        </button>
        <form class="search-field" role="search" @submit.prevent="submitSearch">
          <TdbIcon name="search" :size="19" /><input
            v-model="query"
            class="tdb-input"
            type="search"
            placeholder="Search projects, tasks, or files…"
            aria-label="Global search"
          />
        </form>
        <div class="ml-auto flex items-center gap-2">
          <!-- Theme Toggle -->
          <TdbDropdown>
            <template #trigger>
              <span class="text-lg">{{
                preferredAppearance === 'dark' ? '◐' : preferredAppearance === 'system' ? '◒' : '☀'
              }}</span>
            </template>
            <button
              class="flex w-full items-center gap-2 rounded px-3 py-2 text-left text-sm hover:bg-[var(--surface-2)]"
              @click="setAppearance('light')"
            >
              <TdbIcon
                name="check"
                :size="16"
                class="text-[var(--primary)]"
                :class="{ 'opacity-0': preferredAppearance !== 'light' }"
              />
              <span class="text-base w-4 text-center">☀</span> Light
            </button>
            <button
              class="flex w-full items-center gap-2 rounded px-3 py-2 text-left text-sm hover:bg-[var(--surface-2)]"
              @click="setAppearance('dark')"
            >
              <TdbIcon
                name="check"
                :size="16"
                class="text-[var(--primary)]"
                :class="{ 'opacity-0': preferredAppearance !== 'dark' }"
              />
              <span class="text-base w-4 text-center">◐</span> Dark
            </button>
            <button
              class="flex w-full items-center gap-2 rounded px-3 py-2 text-left text-sm hover:bg-[var(--surface-2)]"
              @click="setAppearance('system')"
            >
              <TdbIcon
                name="check"
                :size="16"
                class="text-[var(--primary)]"
                :class="{ 'opacity-0': preferredAppearance !== 'system' }"
              />
              <span class="text-base w-4 text-center">◒</span> System
            </button>
          </TdbDropdown>
          <div class="topbar-user text-right">
            <strong class="block text-sm">{{ page.props.auth.user.name }}</strong>
            <span class="block text-xs text-[var(--muted)]">Personal workspace</span>
          </div>
        </div>
      </header>
      <main class="page-content"><slot /></main>
    </div>

    <nav class="mobile-bottom" aria-label="Mobile navigation">
      <Link :href="route('dashboard')" :class="{ active: isActive('dashboard') }">
        <TdbIcon name="dashboard" :size="20" /><span>Home</span>
      </Link>
      <Link :href="route('projects.index')" :class="{ active: isActive('projects.index') }">
        <TdbIcon name="projects" :size="20" /><span>Projects</span>
      </Link>
      <Link :href="route('tasks.index')" :class="{ active: isActive('tasks.index') }">
        <TdbIcon name="tasks" :size="20" /><span>Tasks</span>
      </Link>
      <button @click="mobileOpen = true"><TdbIcon name="more" :size="20" /><span>More</span></button>
    </nav>

    <div class="toast-stack" aria-live="polite">
      <Transition name="fade"
        ><div v-if="toast" class="toast" :class="toast.type">
          <TdbIcon :name="toast.type === 'success' ? 'check' : 'close'" :size="20" />
          <p class="flex-1 text-sm font-semibold">{{ toast.message }}</p>
          <button aria-label="Dismiss notification" class="text-[var(--muted)]" @click="toast = null">×</button>
        </div></Transition
      >
    </div>
  </div>
</template>
