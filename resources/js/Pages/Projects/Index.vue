<script setup>
import { reactive, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ProjectCard from '@/Components/Project/ProjectCard.vue';
import TdbButton from '@/Components/UI/TdbButton.vue';
import TdbIcon from '@/Components/UI/TdbIcon.vue';
const props = defineProps({ projects: Object, filters: Object });
const view = ref(props.filters.view ?? localStorage.getItem('tdb-project-view') ?? 'grid');
const form = reactive({
  search: props.filters.search ?? '',
  status: props.filters.status ?? 'all',
  sort: props.filters.sort ?? 'latest',
});
let timer;
const submit = () =>
  router.get(route('projects.index'), { ...form, view: view.value }, { preserveState: true, replace: true });
const search = () => {
  clearTimeout(timer);
  timer = setTimeout(submit, 320);
};
const setView = (value) => {
  view.value = value;
  localStorage.setItem('tdb-project-view', value);
  submit();
};
</script>
<template>
  <AppLayout title="Projects"
    ><div class="mb-7 flex flex-wrap items-end justify-between gap-4">
      <div>
        <h1 class="tdb-heading text-3xl sm:text-4xl">Projects</h1>
        <p class="mt-2 text-[var(--muted)]">Every active commitment, in one clear view.</p>
      </div>
      <TdbButton :href="route('projects.create')"><TdbIcon name="plus" :size="18" /> Create project</TdbButton>
    </div>
    <form
      class="tdb-panel mb-5 grid gap-3 p-4 md:grid-cols-[minmax(220px,1fr)_160px_160px_auto]"
      @submit.prevent="submit"
    >
      <div class="relative">
        <TdbIcon name="search" :size="18" class="absolute left-3 top-1/2 -translate-y-1/2 text-[var(--muted)]" /><input
          v-model="form.search"
          class="tdb-input !pl-10"
          type="search"
          placeholder="Search projects…"
          aria-label="Search projects"
          @input="search"
        />
      </div>
      <select v-model="form.status" class="tdb-input" aria-label="Filter project status" @change="submit">
        <option value="all">All projects</option>
        <option value="active">Active</option>
        <option value="completed">Completed</option>
        <option value="archived">Archived</option></select
      ><select v-model="form.sort" class="tdb-input" aria-label="Sort projects" @change="submit">
        <option value="latest">Latest</option>
        <option value="oldest">Oldest</option>
        <option value="deadline">Deadline</option>
        <option value="progress">Progress</option>
      </select>
      <div class="flex rounded-lg border border-[var(--line)] p-1">
        <button
          type="button"
          class="rounded-md px-3 py-1.5 text-sm font-bold"
          :class="view === 'grid' ? 'bg-[var(--surface-2)] text-[var(--ink)]' : 'text-[var(--muted)]'"
          @click="setView('grid')"
        >
          Grid</button
        ><button
          type="button"
          class="rounded-md px-3 py-1.5 text-sm font-bold"
          :class="view === 'list' ? 'bg-[var(--surface-2)] text-[var(--ink)]' : 'text-[var(--muted)]'"
          @click="setView('list')"
        >
          List
        </button>
      </div>
    </form>
    <div
      v-if="projects.data.length"
      :class="view === 'grid' ? 'grid gap-4 md:grid-cols-2 xl:grid-cols-3' : 'space-y-3'"
    >
      <ProjectCard v-for="project in projects.data" :key="project.id" :project="project" :view="view" />
    </div>
    <section v-else class="tdb-card px-5 py-16 text-center">
      <span class="mx-auto grid h-14 w-14 place-items-center rounded-xl border border-dashed border-[var(--line)]"
        ><TdbIcon name="projects" :size="28"
      /></span>
      <h2 class="tdb-heading mt-4 text-2xl">No projects found.</h2>
      <p class="mx-auto mt-2 max-w-md text-sm text-[var(--muted)]">
        {{
          form.search || form.status !== 'all'
            ? 'Try clearing the search or filters.'
            : 'Start by creating your first project.'
        }}
      </p>
      <TdbButton v-if="!form.search && form.status === 'all'" :href="route('projects.create')" class="mt-5"
        ><TdbIcon name="plus" :size="18" /> Create project</TdbButton
      >
    </section>
    <nav v-if="projects.links.length > 3" class="mt-6 flex flex-wrap justify-center gap-1" aria-label="Project pages">
      <Link
        v-for="link in projects.links"
        :key="link.label"
        :href="link.url || '#'"
        class="tdb-btn tdb-btn-secondary !min-h-9 !px-3"
        :class="{ '!bg-[var(--primary)] !text-white': link.active, 'pointer-events-none opacity-45': !link.url }"
        v-html="link.label"
      /></nav
  ></AppLayout>
</template>
