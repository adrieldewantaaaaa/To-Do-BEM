<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TaskCard from '@/Components/Task/TaskCard.vue';
import TaskDrawer from '@/Components/Task/TaskDrawer.vue';
import TaskFilter from '@/Components/Task/TaskFilter.vue';
import TdbIcon from '@/Components/UI/TdbIcon.vue';
const props = defineProps({ tasks: Object, filters: Object });
const selectedTask = ref(null);
const toggle = (task) =>
  router.patch(
    route('tasks.status', task.id),
    { status: task.status === 'done' ? 'todo' : 'done', position: task.position ?? 0 },
    { preserveScroll: true },
  );
</script>
<template>
  <AppLayout title="Tasks"
    ><div class="mb-7">
      <h1 class="tdb-heading text-3xl sm:text-4xl">Tasks</h1>
      <p class="mt-2 text-[var(--muted)]">Find the next useful action across every project.</p>
    </div>
    <TaskFilter :filters="filters" />
    <div v-if="tasks.data.length" class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
      <TaskCard v-for="task in tasks.data" :key="task.id" :task="task" @open="selectedTask = $event" @toggle="toggle" />
    </div>
    <section v-else class="tdb-card mt-5 px-5 py-16 text-center">
      <span class="mx-auto grid h-14 w-14 place-items-center rounded-xl border border-dashed border-[var(--line)]"
        ><TdbIcon name="tasks" :size="28"
      /></span>
      <h2 class="tdb-heading mt-4 text-2xl">No matching tasks.</h2>
      <p class="mt-2 text-sm text-[var(--muted)]">
        Try changing the search or filters, or add a task from a project board.
      </p>
    </section>
    <nav v-if="tasks.links.length > 3" class="mt-6 flex flex-wrap justify-center gap-1" aria-label="Task pages">
      <Link
        v-for="link in tasks.links"
        :key="link.label"
        :href="link.url || '#'"
        class="tdb-btn tdb-btn-secondary !min-h-9 !px-3"
        :class="{ '!bg-[var(--primary)] !text-white': link.active, 'pointer-events-none opacity-45': !link.url }"
        v-html="link.label"
      />
    </nav>
    <TaskDrawer :show="!!selectedTask" :task="selectedTask" @close="selectedTask = null"
  /></AppLayout>
</template>
