<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TdbBadge from '@/Components/UI/TdbBadge.vue';
import TdbIcon from '@/Components/UI/TdbIcon.vue';
import { formatDate, statusLabel } from '@/lib/format';
const props = defineProps({ q: String, projects: Array, tasks: Array, files: Array });
const query = ref(props.q);
const submit = () => router.get(route('search'), { q: query.value });
</script>
<template>
  <AppLayout title="Search"
    ><div class="mb-7">
      <h1 class="tdb-heading text-3xl sm:text-4xl">Search</h1>
      <p class="mt-2 text-[var(--muted)]">Projects, tasks, and files—grouped so context stays visible.</p>
    </div>
    <form class="tdb-card flex gap-2 p-4" @submit.prevent="submit">
      <div class="relative flex-1">
        <TdbIcon name="search" :size="19" class="absolute left-3 top-1/2 -translate-y-1/2 text-[var(--muted)]" /><input
          v-model="query"
          class="tdb-input !pl-10"
          type="search"
          autofocus
          placeholder="Search your workspace…"
          aria-label="Search workspace"
        />
      </div>
      <button class="tdb-btn tdb-btn-primary">Search</button>
    </form>
    <p v-if="q" class="my-5 text-sm text-[var(--muted)]">
      Results for <strong class="text-[var(--ink)]">“{{ q }}”</strong>
    </p>
    <div v-if="q" class="grid gap-5 xl:grid-cols-3">
      <section class="tdb-card overflow-hidden">
        <header class="flex items-center justify-between border-b border-[var(--line)] px-4 py-3">
          <h2 class="font-extrabold">Projects</h2>
          <span class="text-xs font-bold text-[var(--muted)]">{{ projects.length }}</span>
        </header>
        <div v-if="projects.length" class="divide-y divide-[var(--line)]">
          <Link
            v-for="project in projects"
            :key="project.id"
            :href="route('projects.show', project.id)"
            class="block px-4 py-4 hover:bg-[var(--surface-2)]"
            ><div class="flex items-center justify-between gap-3">
              <strong class="truncate">{{ project.name }}</strong
              ><TdbBadge tone="primary">{{ statusLabel(project.status) }}</TdbBadge>
            </div>
            <p class="mt-2 line-clamp-2 text-sm text-[var(--muted)]">
              {{ project.description || 'No description.' }}
            </p></Link
          >
        </div>
        <p v-else class="p-6 text-center text-sm text-[var(--muted)]">No projects.</p>
      </section>
      <section class="tdb-card overflow-hidden">
        <header class="flex items-center justify-between border-b border-[var(--line)] px-4 py-3">
          <h2 class="font-extrabold">Tasks</h2>
          <span class="text-xs font-bold text-[var(--muted)]">{{ tasks.length }}</span>
        </header>
        <div v-if="tasks.length" class="divide-y divide-[var(--line)]">
          <Link
            v-for="task in tasks"
            :key="task.id"
            :href="route('projects.show', task.project_id)"
            class="block px-4 py-4 hover:bg-[var(--surface-2)]"
            ><strong class="block truncate">{{ task.title }}</strong>
            <p class="mt-1 truncate text-xs text-[var(--muted)]">
              {{ task.project.name }} · {{ formatDate(task.deadline) }}
            </p></Link
          >
        </div>
        <p v-else class="p-6 text-center text-sm text-[var(--muted)]">No tasks.</p>
      </section>
      <section class="tdb-card overflow-hidden">
        <header class="flex items-center justify-between border-b border-[var(--line)] px-4 py-3">
          <h2 class="font-extrabold">Files</h2>
          <span class="text-xs font-bold text-[var(--muted)]">{{ files.length }}</span>
        </header>
        <div v-if="files.length" class="divide-y divide-[var(--line)]">
          <a
            v-for="file in files"
            :key="file.id"
            :href="route('attachments.download', file.id)"
            class="flex items-center gap-3 px-4 py-4 hover:bg-[var(--surface-2)]"
            ><TdbIcon name="paperclip" :size="18" />
            <div class="min-w-0">
              <strong class="block truncate text-sm">{{ file.original_name }}</strong>
              <p class="mt-1 truncate text-xs text-[var(--muted)]">
                {{ file.task.project.name }} · {{ file.human_size }}
              </p>
            </div></a
          >
        </div>
        <p v-else class="p-6 text-center text-sm text-[var(--muted)]">No files.</p>
      </section>
    </div>
    <div v-else class="tdb-card mt-5 px-5 py-16 text-center">
      <TdbIcon name="search" :size="34" class="mx-auto text-[var(--muted)]" />
      <p class="mt-3 text-[var(--muted)]">Enter a term to search your workspace.</p>
    </div></AppLayout
  >
</template>
