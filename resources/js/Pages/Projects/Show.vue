<script setup>
import { computed, reactive, ref, watch, onMounted, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ProjectProgress from '@/Components/Project/ProjectProgress.vue';
import KanbanColumn from '@/Components/Task/KanbanColumn.vue';
import TaskCard from '@/Components/Task/TaskCard.vue';
import TaskCreateForm from '@/Components/Task/TaskCreateForm.vue';
import TaskDrawer from '@/Components/Task/TaskDrawer.vue';
import TdbBadge from '@/Components/UI/TdbBadge.vue';
import TdbButton from '@/Components/UI/TdbButton.vue';
import TdbDropdown from '@/Components/UI/TdbDropdown.vue';
import TdbIcon from '@/Components/UI/TdbIcon.vue';
import TdbModal from '@/Components/UI/TdbModal.vue';
import { formatDate, priorityLabel, statusLabel } from '@/lib/format';
const props = defineProps({
  project: Object,
  canManage: { type: Boolean, default: true },
  roomMembers: { type: Array, default: () => [] },
});
const tab = ref('board');
const addingTask = ref(false);
const selectedTask = ref(null);
const confirmingDelete = ref(false);
const buildBoard = (tasks) => ({
  todo: tasks.filter((t) => t.status === 'todo'),
  in_progress: tasks.filter((t) => t.status === 'in_progress'),
  done: tasks.filter((t) => t.status === 'done'),
});
const board = reactive(buildBoard(props.project.tasks));
watch(
  () => props.project.tasks,
  (tasks) => Object.assign(board, buildBoard(tasks)),
);
const allTasks = computed(() => [...board.todo, ...board.in_progress, ...board.done]);
const allFiles = computed(() =>
  allTasks.value.flatMap((task) => (task.attachments || []).map((file) => ({ ...file, task }))),
);
const columns = [
  { key: 'todo', label: 'To-do' },
  { key: 'in_progress', label: 'In progress' },
  { key: 'done', label: 'Done' },
];
const persistMove = (task, status, position) => {
  task.status = status;
  task.position = position;
  router.patch(
    route('tasks.status', task.id),
    { status, position },
    { preserveScroll: true, preserveState: true, onError: () => router.reload({ only: ['project'] }) },
  );
};
const onChange = (event, status) => {
  const item = event.added?.element || event.moved?.element;
  const index = event.added?.newIndex ?? event.moved?.newIndex;
  if (item && index !== undefined) persistMove(item, status, index);
};
const move = (task, status) => {
  if (task.status === status) return;
  const old = board[task.status];
  const index = old.findIndex((item) => item.id === task.id);
  if (index >= 0) old.splice(index, 1);
  board[status].push(task);
  persistMove(task, status, board[status].length - 1);
};
const toggle = (task) => move(task, task.status === 'done' ? 'todo' : 'done');
const deleteProject = () => router.delete(route('projects.destroy', props.project.id));
const projectTone = (s) => (s === 'completed' ? 'success' : s === 'archived' ? 'neutral' : 'primary');

let pollInterval;
onMounted(() => {
  pollInterval = setInterval(() => {
    // Only poll if the user isn't currently adding or viewing a task
    if (!addingTask.value && !selectedTask.value && !confirmingDelete.value) {
      router.reload({ only: ['project'], preserveScroll: true, preserveState: true });
    }
  }, 5000);
});
onUnmounted(() => clearInterval(pollInterval));
</script>
<template>
  <AppLayout :title="project.name"
    ><div class="mb-6">
      <Link
        v-if="project.room"
        :href="route('rooms.show', project.room.id)"
        class="text-sm font-bold text-[var(--primary)]"
        >← {{ project.room.name }}</Link
      >
      <Link v-else :href="route('projects.index')" class="text-sm font-bold text-[var(--primary)]">← Projects</Link>
      <div class="mt-3 flex flex-wrap items-start justify-between gap-4">
        <div>
          <div class="flex flex-wrap items-center gap-3">
            <h1 class="tdb-heading text-3xl sm:text-4xl">{{ project.name }}</h1>
            <TdbBadge :tone="projectTone(project.status)">{{ statusLabel(project.status) }}</TdbBadge>
          </div>
          <p class="mt-2 max-w-3xl text-[var(--muted)]">{{ project.description || 'No project description yet.' }}</p>
          <p class="mt-3 text-sm">
            <span class="text-[var(--muted)]">Due</span>
            <strong class="ml-1">{{ formatDate(project.deadline) }}</strong>
          </p>
        </div>
        <div class="flex flex-wrap gap-2">
          <TdbButton @click="addingTask = true"><TdbIcon name="plus" :size="18" /> Add task</TdbButton
          ><TdbButton
            v-if="canManage"
            :href="route('projects.edit', project.id)"
            variant="secondary"
            ><TdbIcon name="edit" :size="17" /> Edit</TdbButton
          ><TdbDropdown
            ><template #trigger><TdbIcon name="more" :size="20" /></template
            ><a
              :href="route('projects.export.pdf', project.id)"
              class="block rounded-md px-3 py-2 text-sm font-semibold hover:bg-[var(--surface-2)]"
              >Export PDF</a
            ><a
              :href="route('projects.export.excel', project.id)"
              class="block rounded-md px-3 py-2 text-sm font-semibold hover:bg-[var(--surface-2)]"
              >Export Excel</a
            ><button
              v-if="canManage"
              class="block w-full rounded-md px-3 py-2 text-left text-sm font-semibold text-[var(--danger)] hover:bg-[var(--surface-2)]"
              @click="confirmingDelete = true"
            >
              Delete project
            </button></TdbDropdown
          >
        </div>
      </div>
    </div>
    <section class="tdb-card mb-6 grid gap-5 p-5 md:grid-cols-[minmax(0,1fr)_320px] md:items-center">
      <div>
        <p class="text-xs font-extrabold uppercase tracking-[.12em] text-[var(--muted)]">Project progress</p>
        <h2 class="tdb-heading mt-1 text-2xl">
          {{ project.progress.completed }} of {{ project.progress.total }} tasks complete.
        </h2>
      </div>
      <ProjectProgress :progress="project.progress" />
    </section>
    <div class="mb-5 flex gap-5 border-b border-[var(--line)]" role="tablist">
      <button
        v-for="item in ['board', 'list', 'files']"
        :key="item"
        class="relative px-1 pb-3 text-sm font-extrabold capitalize text-[var(--muted)]"
        :class="{ 'text-[var(--ink)] sketch-underline': tab === item }"
        role="tab"
        :aria-selected="tab === item"
        @click="tab = item"
      >
        {{ item }}
      </button>
    </div>
    <section v-if="tab === 'board'" class="kanban-scroll">
      <div class="kanban-grid">
        <KanbanColumn
          v-for="column in columns"
          :key="column.key"
          :title="column.label"
          :status="column.key"
          :tasks="board[column.key]"
          @change="onChange"
          @open="selectedTask = $event"
          @move="move"
          @toggle="toggle"
        />
      </div>
    </section>
    <section v-else-if="tab === 'list'" class="tdb-card overflow-hidden">
      <div v-if="allTasks.length" class="divide-y divide-[var(--line)]">
        <TaskCard
          v-for="task in allTasks"
          :key="task.id"
          :task="task"
          compact
          @open="selectedTask = $event"
          @toggle="toggle"
        />
      </div>
      <p v-else class="p-12 text-center text-[var(--muted)]">No tasks yet.</p>
    </section>
    <section v-else class="tdb-card overflow-hidden">
      <div v-if="allFiles.length" class="divide-y divide-[var(--line)]">
        <div v-for="file in allFiles" :key="file.id" class="flex items-center gap-3 px-5 py-4">
          <span class="grid h-10 w-10 place-items-center rounded-lg bg-[var(--surface-2)]"
            ><TdbIcon name="paperclip" :size="19"
          /></span>
          <div class="min-w-0 flex-1">
            <strong class="block truncate text-sm">{{ file.original_name }}</strong
            ><span class="text-xs text-[var(--muted)]">{{ file.task.title }} · {{ file.human_size }}</span>
          </div>
          <a :href="route('attachments.download', file.id)" class="tdb-btn tdb-btn-secondary !min-h-9 !px-3"
            ><TdbIcon name="download" :size="16" /><span class="hide-mobile">Download</span></a
          >
        </div>
      </div>
      <p v-else class="p-12 text-center text-[var(--muted)]">No files attached to this project.</p>
    </section>
    <TdbModal :show="addingTask" title="Add a task" @close="addingTask = false">
      <TaskCreateForm :project-id="project.id" :room-members="roomMembers" @saved="addingTask = false" @cancel="addingTask = false" />
    </TdbModal>
    <TaskDrawer
      :show="!!selectedTask"
      :task="selectedTask"
      :project-name="project.name"
      :room-members="roomMembers"
      @close="selectedTask = null"
    /><TdbModal :show="confirmingDelete" title="Delete this project?" @close="confirmingDelete = false"
      ><p class="text-[var(--muted)]">
        This removes the project, every task, and all attachments. This action cannot be undone.
      </p>
      <div class="mt-6 flex justify-end gap-2">
        <TdbButton variant="ghost" @click="confirmingDelete = false">Cancel</TdbButton
        ><TdbButton variant="danger" @click="deleteProject">Delete project</TdbButton>
      </div></TdbModal
    ></AppLayout
  >
</template>
