<script setup>
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ProjectProgress from '@/Components/Project/ProjectProgress.vue';
import SketchCheckbox from '@/Components/Sketch/SketchCheckbox.vue';
import TdbBadge from '@/Components/UI/TdbBadge.vue';
import TdbButton from '@/Components/UI/TdbButton.vue';
import TdbIcon from '@/Components/UI/TdbIcon.vue';
import TaskDrawer from '@/Components/Task/TaskDrawer.vue';
import StatRing from '@/Components/Chart/StatRing.vue';
import StackedBar from '@/Components/Chart/StackedBar.vue';
import MiniBars from '@/Components/Chart/MiniBars.vue';
import SparkLine from '@/Components/Chart/SparkLine.vue';
import { formatDate, priorityLabel, statusLabel } from '@/lib/format';
const props = defineProps({ stats: Object, featured: Object, tasks: Array, upcoming: Array, insights: Object });
const statusColors = { todo: 'var(--muted)', in_progress: 'var(--warning)', done: 'var(--success)' };
const statusSegments = computed(() =>
  (props.insights?.byStatus ?? []).map((s) => ({ label: s.label, value: s.value, color: statusColors[s.key] })),
);
const page = usePage();

const activeTab = ref('all');
const selectedTask = ref(null);
const greeting = computed(() => {
  const h = new Date().getHours();
  return h < 12 ? 'Good morning' : h < 18 ? 'Good afternoon' : 'Good evening';
});
const tabs = [
  { key: 'all', label: 'All' },
  { key: 'todo', label: 'To-do' },
  { key: 'in_progress', label: 'In progress' },
  { key: 'done', label: 'Done' },
];
const filteredTasks = computed(() =>
  activeTab.value === 'all' ? props.tasks : props.tasks.filter((t) => t.status === activeTab.value),
);
const cards = computed(() => [
  { label: 'Total projects', value: props.stats.projects, icon: 'projects', tone: '#2383E2' },
  { label: 'Total tasks', value: props.stats.tasks, icon: 'tasks', tone: '#787774' },
  { label: 'In progress', value: props.stats.in_progress, icon: 'calendar', tone: '#CB912F' },
  { label: 'Completed', value: props.stats.completed, icon: 'check', tone: '#448361' },
]);
const toggle = (task) =>
  router.patch(
    route('tasks.status', task.id),
    { status: task.status === 'done' ? 'todo' : 'done', position: task.position ?? 0 },
    { preserveScroll: true },
  );
const priorityTone = (p) => (p === 'high' ? 'danger' : p === 'medium' ? 'warning' : 'neutral');
const deadlineTone = (state) =>
  state === 'overdue' ? 'danger' : state === 'today' ? 'warning' : state === 'upcoming' ? 'primary' : 'neutral';
</script>
<template>
  <AppLayout :title="'Dashboard'"
    ><div class="mb-7 flex flex-wrap items-end justify-between gap-4">
      <div>
        <h1 class="tdb-heading text-3xl sm:text-4xl">{{ greeting }}, {{ page.props.auth.user.name.split(' ')[0] }}.</h1>
        <p class="mt-2 text-[var(--muted)]">{{ 'Here’s what needs your attention today.' }}</p>
      </div>
      <TdbButton :href="route('projects.create')"><TdbIcon name="plus" :size="18" /> {{ 'New project' }}</TdbButton>
    </div>
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Workspace statistics">
      <article v-for="card in cards" :key="card.label" class="tdb-card flex items-center gap-4 p-5">
        <span
          class="grid h-11 w-11 place-items-center rounded-[11px_8px_12px_9px]"
          :style="{ background: `color-mix(in srgb, ${card.tone} 13%, var(--surface))`, color: card.tone }"
          ><TdbIcon :name="card.icon" :size="22"
        /></span>
        <div>
          <p class="text-sm text-[var(--muted)]">{{ card.label }}</p>
          <strong class="tdb-heading text-3xl" data-countup>{{ card.value }}</strong>
        </div>
      </article>
    </section>
    <section class="mt-4 grid gap-4 lg:grid-cols-3" aria-label="Insights">
      <article class="tdb-card flex flex-col p-5">
        <p class="text-xs font-bold uppercase tracking-[.1em] text-[var(--muted)]">{{ 'Overall completion' }}</p>
        <div class="mt-3 flex items-center gap-4">
          <StatRing :value="insights.completion" :size="118" :stroke="11" />
          <div>
            <p class="tdb-heading text-2xl tabular-nums">
              {{ insights.done }} <span class="text-[var(--muted)]">/ {{ insights.total }}</span>
            </p>
            <p class="text-sm text-[var(--muted)]">{{ 'tasks completed' }}</p>
          </div>
        </div>
        <div class="mt-auto border-t border-[var(--line)] pt-3">
          <p class="mb-1.5 text-xs text-[var(--muted)]">{{ 'Completed · last 14 days' }}</p>
          <SparkLine :points="insights.completedTrend" color="var(--success)" />
        </div>
      </article>
      <article class="tdb-card p-5">
        <p class="text-xs font-bold uppercase tracking-[.1em] text-[var(--muted)]">{{ 'Tasks by status' }}</p>
        <div class="mt-4"><StackedBar :segments="statusSegments" /></div>
      </article>
      <article class="tdb-card p-5">
        <p class="text-xs font-bold uppercase tracking-[.1em] text-[var(--muted)]">{{ 'Next 7 days' }}</p>
        <p class="mt-1 text-sm text-[var(--muted)]">{{ 'Upcoming deadlines' }}</p>
        <div class="mt-4"><MiniBars :bars="insights.deadlines7" color="var(--primary)" :highlight="0" /></div>
      </article>
    </section>
    <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1.65fr)_minmax(300px,.8fr)]">
      <div class="space-y-6">
        <section class="tdb-card overflow-hidden">
          <div class="flex items-center justify-between border-b border-[var(--line)] px-5 py-4">
            <div>
              <p class="text-xs font-extrabold uppercase tracking-[.12em] text-[var(--primary)]">{{ 'Featured project' }}</p>
              <h2 class="tdb-heading mt-1 text-2xl">{{ 'Stay close to the finish.' }}</h2>
            </div>
            <Link
              v-if="featured"
              :href="route('projects.show', featured.id)"
              class="text-sm font-bold text-[var(--primary)]"
              >{{ 'View project →' }}</Link
            >
          </div>
          <div v-if="featured" class="grid gap-6 p-5 md:grid-cols-[minmax(0,1fr)_260px]">
            <div>
              <div class="mb-3 flex flex-wrap items-center gap-2">
                <h3 class="text-xl font-extrabold">{{ featured.name }}</h3>
                <TdbBadge tone="primary">{{ statusLabel(featured.status) }}</TdbBadge>
              </div>
              <p class="max-w-2xl text-sm leading-6 text-[var(--muted)]">
                {{ featured.description || 'No project description yet.' }}
              </p>
              <p class="mt-5 text-sm">
                <span class="text-[var(--muted)]">{{ 'Deadline' }}</span>
                <strong class="ml-2">{{ formatDate(featured.deadline) }}</strong>
              </p>
            </div>
            <div class="rounded-[12px_9px_13px_10px] border border-[var(--line)] bg-[var(--surface-2)] p-4">
              <ProjectProgress :progress="featured.progress" /><TdbButton
                :href="route('projects.show', featured.id)"
                variant="secondary"
                class="mt-5 w-full"
                >{{ 'Open board →' }}</TdbButton
              >
            </div>
          </div>
          <div v-else class="p-7 text-center">
            <span class="mx-auto grid h-12 w-12 place-items-center rounded-xl border border-dashed border-[var(--line)]"
              ><TdbIcon name="projects"
            /></span>
            <h3 class="mt-3 font-bold">{{ 'No active project yet.' }}</h3>
            <p class="mt-1 text-sm text-[var(--muted)]">{{ 'Create one to start organizing your tasks.' }}</p>
            <TdbButton :href="route('projects.create')" class="mt-4">{{ 'Create project' }}</TdbButton>
          </div>
        </section>
        <section class="tdb-card">
          <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[var(--line)] px-5 py-4">
            <h2 class="tdb-heading text-2xl">{{ 'My tasks' }}</h2>
            <div class="flex flex-wrap gap-1" role="tablist">
              <button
                v-for="tab in tabs"
                :key="tab.key"
                class="relative rounded-lg px-3 py-1.5 text-sm font-bold text-[var(--muted)]"
                :class="{ 'text-[var(--ink)] sketch-underline': activeTab === tab.key }"
                role="tab"
                :aria-selected="activeTab === tab.key"
                @click="activeTab = tab.key"
              >
                {{ tab.label }}
              </button>
            </div>
          </div>
          <div v-if="filteredTasks.length" class="divide-y divide-[var(--line)]">
            <div
              v-for="task in filteredTasks"
              :key="task.id"
              class="flex items-center gap-3 px-5 py-4 transition hover:bg-[var(--surface-2)]"
            >
              <SketchCheckbox :checked="task.status === 'done'" @change="toggle(task)" /><button
                class="min-w-0 flex-1 text-left"
                @click="selectedTask = task"
              >
                <strong class="block truncate" :class="{ 'strike-drawn w-fit': task.status === 'done' }">{{
                  task.title
                }}</strong
                ><span class="mt-1 block text-xs text-[var(--muted)]">{{ task.project.name }}</span></button
              ><TdbBadge class="hide-mobile" :tone="priorityTone(task.priority)">{{
                priorityLabel(task.priority)
              }}</TdbBadge
              ><span class="text-sm font-semibold" :class="{ 'danger-circle': task.deadline_state === 'overdue' }">{{
                formatDate(task.deadline, { short: true })
              }}</span
              ><span class="hide-mobile inline-flex items-center gap-1 text-xs text-[var(--muted)]"
                ><TdbIcon name="paperclip" :size="15" />{{ task.attachments?.length ?? 0 }}</span
              >
            </div>
          </div>
          <div v-else class="px-5 py-12 text-center text-sm text-[var(--muted)]">{{ 'No tasks in this view.' }}</div>
          <div class="border-t border-[var(--line)] px-5 py-3 text-right">
            <Link :href="route('tasks.index')" class="text-sm font-bold text-[var(--primary)]">{{ 'View all tasks →' }}</Link>
          </div>
        </section>
      </div>
      <aside class="tdb-card h-fit">
        <div class="border-b border-[var(--line)] px-5 py-4">
          <h2 class="tdb-heading text-2xl">{{ 'Upcoming deadlines' }}</h2>
          <p class="mt-1 text-sm text-[var(--muted)]">{{ 'The next five items on your radar.' }}</p>
        </div>
        <div v-if="upcoming.length" class="divide-y divide-[var(--line)]">
          <button
            v-for="task in upcoming"
            :key="task.id"
            class="block w-full px-5 py-4 text-left transition hover:bg-[var(--surface-2)]"
            @click="selectedTask = task"
          >
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <strong class="block truncate text-sm">{{ task.title }}</strong
                ><span class="mt-1 block truncate text-xs text-[var(--muted)]">{{ task.project.name }}</span>
              </div>
              <TdbBadge :tone="deadlineTone(task.deadline_state)">{{ task.deadline_state }}</TdbBadge>
            </div>
            <p class="mt-3 text-xs font-bold">{{ formatDate(task.deadline) }}</p>
          </button>
        </div>
        <p v-else class="p-8 text-center text-sm text-[var(--muted)]">{{ 'No upcoming deadlines.' }}</p>
      </aside>
    </div>
    <TaskDrawer :show="!!selectedTask" :task="selectedTask" @close="selectedTask = null"
  /></AppLayout>
</template>
