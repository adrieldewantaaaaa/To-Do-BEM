<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ProjectProgress from '@/Components/Project/ProjectProgress.vue';
import TdbIcon from '@/Components/UI/TdbIcon.vue';
import { formatDate } from '@/lib/format';
const props = defineProps({ summary: Object, byStatus: Object, projects: Array });
const total = Math.max(1, props.summary.tasks);
const bars = [
  { key: 'todo', label: 'To-do', value: props.byStatus.todo, color: 'var(--muted)' },
  { key: 'in_progress', label: 'In progress', value: props.byStatus.in_progress, color: 'var(--warning)' },
  { key: 'done', label: 'Done', value: props.byStatus.done, color: 'var(--success)' },
];
</script>
<template>
  <AppLayout title="Reports"
    ><div class="mb-7">
      <h1 class="tdb-heading text-3xl sm:text-4xl">Reports</h1>
      <p class="mt-2 text-[var(--muted)]">A quiet read on throughput, overdue work, and project momentum.</p>
    </div>
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
      <article
        v-for="item in [
          { label: 'Projects', value: summary.projects },
          { label: 'Tasks', value: summary.tasks },
          { label: 'Completed', value: summary.completed },
          { label: 'Completion rate', value: `${summary.completion_rate}%` },
          { label: 'Overdue', value: summary.overdue },
        ]"
        :key="item.label"
        class="tdb-card p-5"
      >
        <p class="text-sm text-[var(--muted)]">{{ item.label }}</p>
        <strong class="tdb-heading mt-1 block text-3xl">{{ item.value }}</strong>
      </article>
    </section>
    <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(300px,.72fr)_minmax(0,1.28fr)]">
      <section class="tdb-card p-5">
        <h2 class="tdb-heading text-2xl">Tasks by status</h2>
        <p class="mt-1 text-sm text-[var(--muted)]">Current distribution across your workspace.</p>
        <div
          class="mt-7 space-y-5"
          role="img"
          :aria-label="`Task status: ${byStatus.todo} to-do, ${byStatus.in_progress} in progress, ${byStatus.done} done`"
        >
          <div v-for="bar in bars" :key="bar.key">
            <div class="mb-2 flex justify-between text-sm">
              <span class="font-bold">{{ bar.label }}</span
              ><span class="text-[var(--muted)]">{{ bar.value }} · {{ Math.round((bar.value / total) * 100) }}%</span>
            </div>
            <div class="h-4 overflow-hidden rounded-full border border-[var(--line)] bg-[var(--surface-2)]">
              <div
                class="h-full rounded-full transition-[width]"
                :style="{ width: `${(bar.value / total) * 100}%`, background: bar.color }"
              />
            </div>
          </div>
        </div>
      </section>
      <section class="tdb-card overflow-hidden">
        <header class="border-b border-[var(--line)] px-5 py-4">
          <h2 class="tdb-heading text-2xl">Project progress</h2>
          <p class="mt-1 text-sm text-[var(--muted)]">Nearest deadlines first.</p>
        </header>
        <div v-if="projects.length" class="divide-y divide-[var(--line)]">
          <div
            v-for="project in projects"
            :key="project.id"
            class="grid gap-4 px-5 py-4 md:grid-cols-[minmax(0,1fr)_240px] md:items-center"
          >
            <div class="min-w-0">
              <a :href="route('projects.show', project.id)" class="font-bold hover:text-[var(--primary)]">{{
                project.name
              }}</a>
              <p class="mt-1 text-xs text-[var(--muted)]">Due {{ formatDate(project.deadline) }}</p>
            </div>
            <ProjectProgress :progress="project.progress" />
          </div>
        </div>
        <p v-else class="p-10 text-center text-[var(--muted)]">No project data yet.</p>
      </section>
    </div></AppLayout
  >
</template>
