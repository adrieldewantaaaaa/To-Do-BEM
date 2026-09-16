<script setup>
import { Link } from '@inertiajs/vue3';
import TdbBadge from '@/Components/UI/TdbBadge.vue';
import ProjectProgress from './ProjectProgress.vue';
import { formatDate, statusLabel } from '@/lib/format';
defineProps({ project: { type: Object, required: true }, view: { type: String, default: 'grid' } });
const tone = (status) => (status === 'completed' ? 'success' : status === 'archived' ? 'neutral' : 'primary');
</script>
<template>
  <Link
    :href="route('projects.show', project.id)"
    class="tdb-card group block p-5 transition hover:-translate-y-0.5 hover:border-[color-mix(in_srgb,var(--primary)_45%,var(--line))]"
    :class="{ 'sm:grid sm:grid-cols-[minmax(0,1fr)_170px_160px] sm:items-center sm:gap-6': view === 'list' }"
    ><div class="min-w-0">
      <div class="mb-2 flex items-start justify-between gap-3">
        <h3 class="truncate text-lg font-bold group-hover:text-[var(--primary)]">{{ project.name }}</h3>
        <TdbBadge :tone="tone(project.status)">{{ statusLabel(project.status) }}</TdbBadge>
      </div>
      <p class="line-clamp-2 min-h-[3rem] text-sm text-[var(--muted)]" :class="{ 'sm:min-h-0': view === 'list' }">
        {{ project.description || 'No description yet.' }}
      </p>
    </div>
    <div class="mt-5" :class="{ 'sm:mt-0': view === 'list' }"><ProjectProgress :progress="project.progress" /></div>
    <div
      class="mt-4 flex items-center justify-between border-t border-[var(--line)] pt-4 text-sm"
      :class="{ 'sm:mt-0 sm:block sm:border-l sm:border-t-0 sm:pl-5 sm:pt-0': view === 'list' }"
    >
      <span class="text-[var(--muted)]">Due</span
      ><strong class="sm:block sm:mt-1">{{ formatDate(project.deadline) }}</strong>
    </div></Link
  >
</template>
