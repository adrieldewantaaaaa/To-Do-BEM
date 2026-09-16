<script setup>
import { ref, watch } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TaskCard from '@/Components/Task/TaskCard.vue';
import TaskDrawer from '@/Components/Task/TaskDrawer.vue';
import TaskFilter from '@/Components/Task/TaskFilter.vue';
import TdbIcon from '@/Components/UI/TdbIcon.vue';
import TdbBadge from '@/Components/UI/TdbBadge.vue';

const props = defineProps({ tasks: Object, filters: Object, rooms: Array });
const selectedTask = ref(null);

const toggle = (task) =>
  router.patch(
    route('tasks.status', task.id),
    { status: task.status === 'done' ? 'todo' : 'done', position: task.position ?? 0 },
    { preserveScroll: true },
  );

const filterRoom = (roomId) => {
  const q = { ...props.filters, room: roomId };
  if (!roomId || roomId === 'all') delete q.room;
  router.get(route('my-tasks'), q, { preserveState: true, replace: true });
};
</script>

<template>
  <AppLayout title="My Tasks">
    <div class="mb-7">
      <h1 class="tdb-heading text-3xl sm:text-4xl">My Tasks</h1>
      <p class="mt-2 text-[var(--muted)]">All tasks assigned to you, across every room and project.</p>
    </div>

    <!-- Additional filter for rooms in My Tasks -->
    <div v-if="rooms.length" class="mb-4 flex items-center gap-2 overflow-x-auto pb-2 kanban-scroll">
      <button
        class="whitespace-nowrap rounded-full border px-3 py-1.5 text-sm font-semibold transition-all"
        :class="(!filters.room || filters.room === 'all')
          ? 'border-[var(--primary)] bg-[var(--primary)]/10 text-[var(--primary)]'
          : 'border-[var(--line)] text-[var(--muted)] hover:border-[var(--primary)] hover:text-[var(--ink)]'"
        @click="filterRoom('all')"
      >
        All tasks
      </button>
      <button
        v-for="room in rooms"
        :key="room.id"
        class="whitespace-nowrap rounded-full border px-3 py-1.5 text-sm font-semibold transition-all"
        :class="filters.room == room.id
          ? 'border-[var(--primary)] bg-[var(--primary)]/10 text-[var(--primary)]'
          : 'border-[var(--line)] text-[var(--muted)] hover:border-[var(--primary)] hover:text-[var(--ink)]'"
        @click="filterRoom(room.id)"
      >
        {{ room.name }}
      </button>
    </div>

    <TaskFilter :filters="filters" submit-route="my-tasks" />

    <div v-if="tasks.data.length" class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
      <div v-for="task in tasks.data" :key="task.id" class="relative">
        <!-- Optional room badge above the card to indicate source -->
        <div v-if="task.project?.room" class="absolute -top-2.5 right-3 z-10">
          <TdbBadge tone="neutral" class="shadow-sm border border-[var(--line)] bg-[var(--surface)] text-[10px]">
            {{ task.project.room.name }}
          </TdbBadge>
        </div>
        <TaskCard :task="task" @open="selectedTask = $event" @toggle="toggle" />
      </div>
    </div>
    
    <section v-else class="tdb-card mt-5 px-5 py-16 text-center">
      <span class="mx-auto grid h-14 w-14 place-items-center rounded-xl border border-dashed border-[var(--line)]">
        <TdbIcon name="tasks" :size="28" />
      </span>
      <h2 class="tdb-heading mt-4 text-2xl">No matching tasks.</h2>
      <p class="mt-2 text-sm text-[var(--muted)]">
        Try changing the search or filters, or ask your team to assign tasks to you.
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

    <!-- Empty roomMembers passed since My Tasks is global view, we edit assignees in the project view -->
    <TaskDrawer :show="!!selectedTask" :task="selectedTask" :room-members="[]" @close="selectedTask = null" />
  </AppLayout>
</template>
