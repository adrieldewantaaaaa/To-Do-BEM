<script setup>
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TdbBadge from '@/Components/UI/TdbBadge.vue';
import TdbIcon from '@/Components/UI/TdbIcon.vue';
const props = defineProps({ events: Array });
const view = ref('month');
const cursor = ref(new Date());
const dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
const iso = (d) => {
  const x = new Date(d);
  x.setMinutes(x.getMinutes() - x.getTimezoneOffset());
  return x.toISOString().slice(0, 10);
};
const label = computed(() => new Intl.DateTimeFormat('en', { month: 'long', year: 'numeric' }).format(cursor.value));
const startOfWeek = (d) => {
  const copy = new Date(d);
  const day = (copy.getDay() + 6) % 7;
  copy.setDate(copy.getDate() - day);
  copy.setHours(0, 0, 0, 0);
  return copy;
};
const monthDays = computed(() => {
  const first = new Date(cursor.value.getFullYear(), cursor.value.getMonth(), 1);
  const start = startOfWeek(first);
  return Array.from({ length: 42 }, (_, i) => {
    const d = new Date(start);
    d.setDate(start.getDate() + i);
    return d;
  });
});
const weekDays = computed(() => {
  const start = startOfWeek(cursor.value);
  return Array.from({ length: 7 }, (_, i) => {
    const d = new Date(start);
    d.setDate(start.getDate() + i);
    return d;
  });
});
const days = computed(() => (view.value === 'month' ? monthDays.value : weekDays.value));
const eventsFor = (d) => props.events.filter((event) => String(event.date).slice(0, 10) === iso(d));
const shift = (amount) => {
  const d = new Date(cursor.value);
  view.value === 'month' ? d.setMonth(d.getMonth() + amount) : d.setDate(d.getDate() + amount * 7);
  cursor.value = d;
};
const eventTone = (event) =>
  event.type === 'project'
    ? 'primary'
    : event.status === 'done'
      ? 'success'
      : event.priority === 'high'
        ? 'danger'
        : 'warning';
</script>
<template>
  <AppLayout title="Calendar"
    ><div class="mb-7 flex flex-wrap items-end justify-between gap-4">
      <div>
        <h1 class="tdb-heading text-3xl sm:text-4xl">Calendar</h1>
        <p class="mt-2 text-[var(--muted)]">Project and task deadlines, without losing their context.</p>
      </div>
      <div class="flex rounded-lg border border-[var(--line)] bg-[var(--surface)] p-1">
        <button
          v-for="option in ['month', 'week']"
          :key="option"
          class="rounded-md px-3 py-1.5 text-sm font-bold capitalize"
          :class="view === option ? 'bg-[var(--surface-2)] text-[var(--ink)]' : 'text-[var(--muted)]'"
          @click="view = option"
        >
          {{ option }}
        </button>
      </div>
    </div>
    <section class="tdb-card overflow-hidden">
      <header class="flex items-center justify-between gap-3 border-b border-[var(--line)] px-4 py-3">
        <button class="tdb-btn tdb-btn-ghost !min-h-9 !px-2" aria-label="Previous period" @click="shift(-1)">
          <TdbIcon name="chevron-left" />
        </button>
        <div class="text-center">
          <h2 class="tdb-heading text-xl sm:text-2xl">{{ label }}</h2>
          <button class="text-xs font-bold text-[var(--primary)]" @click="cursor = new Date()">Today</button>
        </div>
        <button class="tdb-btn tdb-btn-ghost !min-h-9 !px-2" aria-label="Next period" @click="shift(1)">
          <TdbIcon name="chevron-right" />
        </button>
      </header>
      <div class="overflow-x-auto">
        <div class="min-w-[760px]">
          <div class="grid grid-cols-7 border-b border-[var(--line)] bg-[var(--surface-2)]">
            <div
              v-for="day in dayNames"
              :key="day"
              class="px-3 py-2 text-center text-xs font-extrabold uppercase tracking-wide text-[var(--muted)]"
            >
              {{ day }}
            </div>
          </div>
          <div class="grid grid-cols-7">
            <div
              v-for="day in days"
              :key="iso(day)"
              class="min-h-28 border-b border-r border-[var(--line)] p-2"
              :class="{ 'bg-[var(--surface-2)] opacity-65': view === 'month' && day.getMonth() !== cursor.getMonth() }"
            >
              <div class="mb-2 flex items-center justify-between">
                <span
                  class="grid h-7 w-7 place-items-center rounded-full text-sm font-bold"
                  :class="{ 'bg-[#2563EB] text-white': iso(day) === iso(new Date()) }"
                  >{{ day.getDate() }}</span
                ><span v-if="eventsFor(day).length > 3" class="text-xs text-[var(--muted)]"
                  >+{{ eventsFor(day).length - 3 }}</span
                >
              </div>
              <div class="space-y-1">
                <Link
                  v-for="event in eventsFor(day).slice(0, 3)"
                  :key="event.id"
                  :href="event.url || event.project_url"
                  class="block truncate rounded-[6px_4px_7px_5px] border px-2 py-1 text-xs font-bold"
                  :class="{
                    'border-blue-200 bg-blue-50 text-blue-800 dark:border-blue-900 dark:bg-blue-950 dark:text-blue-100':
                      eventTone(event) === 'primary',
                    'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-100':
                      eventTone(event) === 'success',
                    'border-red-200 bg-red-50 text-red-800 dark:border-red-900 dark:bg-red-950 dark:text-red-100':
                      eventTone(event) === 'danger',
                    'border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-900 dark:bg-amber-950 dark:text-amber-100':
                      eventTone(event) === 'warning',
                  }"
                  ><span class="mr-1">{{ event.type === 'project' ? '◆' : '□' }}</span
                  >{{ event.title }}</Link
                >
              </div>
            </div>
          </div>
        </div>
      </div>
      <footer class="flex flex-wrap gap-4 px-4 py-3 text-xs font-semibold text-[var(--muted)]">
        <span>◆ Project deadline</span><span>□ Task deadline</span
        ><span>Status is also shown by label and shape—not color alone.</span>
      </footer>
    </section></AppLayout
  >
</template>
