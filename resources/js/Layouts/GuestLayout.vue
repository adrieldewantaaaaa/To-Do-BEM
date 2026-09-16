<script setup>
import { computed, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import TdbDropdown from '@/Components/UI/TdbDropdown.vue';

const preferredAppearance = computed(
  () => localStorage.getItem('tdb-appearance') ?? 'system',
);

const applyAppearance = (choice) => {
  localStorage.setItem('tdb-appearance', choice);
  const dark = choice === 'dark' || (choice === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
  document.documentElement.classList.toggle('dark', dark);
  // We don't have reactivity on localStorage directly, so we reload page or let it stay since it's just visually updating DOM
};

const setAppearance = (choice) => {
  applyAppearance(choice);
  // Optional: reload the page to update preferredAppearance computed if needed, but it works without it if we just use local variable, but since we use localstorage directly in computed it's fine
  window.location.reload(); // Simple way to ensure computed updates and styles apply fully
};

onMounted(() => {
  applyAppearance(preferredAppearance.value);
});
</script>
<template>
  <main class="grid min-h-screen place-items-center bg-[var(--paper)] px-4 py-10 relative">
    <div class="absolute top-4 right-4 flex gap-2">
      <!-- Theme Toggle -->
      <TdbDropdown>
        <template #trigger>
          <span class="text-lg">{{ preferredAppearance === 'dark' ? '◐' : preferredAppearance === 'system' ? '◒' : '☀' }}</span>
        </template>
        <button type="button" class="flex w-full items-center gap-2 rounded px-3 py-2 text-left text-sm hover:bg-[var(--surface-2)]" @click="setAppearance('light')">
          <span class="text-base w-4 text-center text-[var(--primary)]" :class="{ 'opacity-0': preferredAppearance !== 'light' }">✓</span>
          <span class="text-base w-4 text-center">☀</span> {{ 'Light' }}
        </button>
        <button type="button" class="flex w-full items-center gap-2 rounded px-3 py-2 text-left text-sm hover:bg-[var(--surface-2)]" @click="setAppearance('dark')">
          <span class="text-base w-4 text-center text-[var(--primary)]" :class="{ 'opacity-0': preferredAppearance !== 'dark' }">✓</span>
          <span class="text-base w-4 text-center">◐</span> {{ 'Dark' }}
        </button>
        <button type="button" class="flex w-full items-center gap-2 rounded px-3 py-2 text-left text-sm hover:bg-[var(--surface-2)]" @click="setAppearance('system')">
          <span class="text-base w-4 text-center text-[var(--primary)]" :class="{ 'opacity-0': preferredAppearance !== 'system' }">✓</span>
          <span class="text-base w-4 text-center">◒</span> {{ 'System' }}
        </button>
      </TdbDropdown>
    </div>

    <section class="w-full max-w-md">
      <Link href="/" class="mx-auto mb-7 flex w-fit items-center gap-3">
        <img src="/logo.png" alt="To-Do BEM Logo" class="h-10 w-auto" />
        <span class="block">
          <strong class="tdb-heading block text-xl">To-Do BEM</strong>
        </span>
      </Link>
      <div class="tdb-card p-6 sm:p-8"><slot /></div>
    </section>
  </main>
</template>
