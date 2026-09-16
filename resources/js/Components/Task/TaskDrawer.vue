<script setup>
import { ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import TdbBadge from '@/Components/UI/TdbBadge.vue';
import TdbButton from '@/Components/UI/TdbButton.vue';
import TdbDrawer from '@/Components/UI/TdbDrawer.vue';
import TdbIcon from '@/Components/UI/TdbIcon.vue';
import TdbModal from '@/Components/UI/TdbModal.vue';
import { formatDate, priorityLabel, statusLabel } from '@/lib/format';

const props = defineProps({
  show: Boolean,
  task: Object,
  projectName: String,
  roomMembers: { type: Array, default: () => [] },
});
const emit = defineEmits(['close']);
const editing = ref(false);
const confirmingDelete = ref(false);
const fileError = ref('');

const form = useForm({
  _method: 'put',
  title: '',
  description: '',
  deadline: '',
  status: 'todo',
  priority: 'medium',
  attachments: [],
  assignees: [],
});

const hydrate = () => {
  if (!props.task) return;
  form.defaults({
    _method: 'put',
    title: props.task.title,
    description: props.task.description ?? '',
    deadline: String(props.task.deadline).slice(0, 10),
    status: props.task.status,
    priority: props.task.priority,
    attachments: [],
    assignees: (props.task.assignees || []).map((u) => u.id),
  });
  form.reset();
  form.clearErrors();
  editing.value = false;
  fileError.value = '';
};

watch(() => props.task?.id, hydrate, { immediate: true });

const chooseFiles = (e) => {
  const files = [...e.target.files];
  const allowed = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'png', 'jpg', 'jpeg', 'zip'];
  const invalid = files.find(
    (f) => f.size > 10 * 1024 * 1024 || !allowed.includes(f.name.split('.').pop().toLowerCase()),
  );
  fileError.value = invalid ? `${invalid.name} must be an allowed file under 10 MB.` : '';
  form.attachments = invalid ? [] : files;
};

const toggleAssignee = (id) => {
  const idx = form.assignees.indexOf(id);
  if (idx >= 0) form.assignees.splice(idx, 1);
  else form.assignees.push(id);
};

const save = () => {
  if (fileError.value) return;
  form.post(route('tasks.update', props.task.id), {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      editing.value = false;
      emit('close');
    },
  });
};

const markDone = () =>
  router.patch(
    route('tasks.status', props.task.id),
    { status: props.task.status === 'done' ? 'todo' : 'done', position: props.task.position ?? 0 },
    { preserveScroll: true, onSuccess: () => emit('close') },
  );

const remove = () =>
  router.delete(route('tasks.destroy', props.task.id), {
    preserveScroll: true,
    onSuccess: () => {
      confirmingDelete.value = false;
      emit('close');
    },
  });

const removeAttachment = (id) => router.delete(route('attachments.destroy', id), { preserveScroll: true });
const tone = (status) => (status === 'done' ? 'success' : status === 'in_progress' ? 'warning' : 'neutral');
</script>

<template>
  <TdbDrawer :show="show" :title="task?.title ?? 'Task details'" @close="$emit('close')">
    <div v-if="task" class="space-y-6 p-5">
      <div class="flex flex-wrap items-center gap-2">
        <TdbBadge :tone="tone(task.status)">{{ statusLabel(task.status) }}</TdbBadge>
        <TdbBadge :tone="task.priority === 'high' ? 'danger' : task.priority === 'medium' ? 'warning' : 'neutral'">
          {{ priorityLabel(task.priority) }}
        </TdbBadge>
        <span class="ml-auto text-sm text-[var(--muted)]">Due {{ formatDate(task.deadline) }}</span>
      </div>

      <div class="rounded-[10px_8px_11px_7px] border border-[var(--line)] bg-[var(--surface-2)] px-4 py-3">
        <span class="text-xs font-bold uppercase tracking-wide text-[var(--muted)]">Project</span>
        <p class="mt-1 font-semibold">{{ task.project?.name || projectName }}</p>
      </div>

      <form v-if="editing" class="space-y-4" @submit.prevent="save">
        <div>
          <label for="drawer-title" class="tdb-label">Title</label>
          <input id="drawer-title" v-model="form.title" class="tdb-input" maxlength="200" required />
          <p v-if="form.errors.title" class="mt-1 text-sm text-[var(--danger)]">{{ form.errors.title }}</p>
        </div>
        <div>
          <label for="drawer-description" class="tdb-label">Description</label>
          <textarea
            id="drawer-description"
            v-model="form.description"
            class="tdb-input min-h-32 resize-y"
            maxlength="5000"
          />
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="tdb-label" for="drawer-status">Status</label>
            <select id="drawer-status" v-model="form.status" class="tdb-input">
              <option value="todo">To-do</option>
              <option value="in_progress">In progress</option>
              <option value="done">Done</option>
            </select>
          </div>
          <div>
            <label class="tdb-label" for="drawer-priority">Priority</label>
            <select id="drawer-priority" v-model="form.priority" class="tdb-input">
              <option value="low">Low</option>
              <option value="medium">Medium</option>
              <option value="high">High</option>
            </select>
          </div>
        </div>
        <div>
          <label class="tdb-label" for="drawer-deadline">Deadline</label>
          <input id="drawer-deadline" v-model="form.deadline" class="tdb-input" type="date" required />
        </div>
        
        <div v-if="roomMembers.length">
          <label class="tdb-label">Assign to</label>
          <div class="mt-1 flex flex-wrap gap-2">
            <button
              v-for="member in roomMembers"
              :key="member.id"
              type="button"
              class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-sm font-semibold transition-all"
              :class="form.assignees.includes(member.id)
                ? 'border-[var(--primary)] bg-[var(--primary)]/10 text-[var(--primary)]'
                : 'border-[var(--line)] text-[var(--muted)] hover:border-[var(--primary)] hover:text-[var(--ink)]'"
              @click="toggleAssignee(member.id)"
            >
              <span
                class="grid h-6 w-6 flex-none place-items-center rounded-full text-xs font-bold"
                :class="form.assignees.includes(member.id) ? 'bg-[var(--primary)] text-white' : 'bg-[var(--surface-2)]'"
              >{{ member.name.charAt(0).toUpperCase() }}</span>
              {{ member.name }}
            </button>
          </div>
          <p v-if="form.errors.assignees" class="mt-1 text-sm text-[var(--danger)]">{{ form.errors.assignees }}</p>
        </div>

        <div>
          <label class="tdb-label" for="drawer-files">Add attachments</label>
          <input
            id="drawer-files"
            class="tdb-input !py-2 text-sm"
            type="file"
            multiple
            accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.zip"
            @change="chooseFiles"
          />
          <p v-if="fileError" class="mt-1 text-sm text-[var(--danger)]">{{ fileError }}</p>
        </div>
        <div class="flex justify-end gap-2">
          <TdbButton variant="ghost" type="button" @click="editing = false">Cancel</TdbButton>
          <TdbButton type="submit" :disabled="form.processing">Save task</TdbButton>
        </div>
      </form>

      <template v-else>
        <section v-if="task.assignees?.length">
          <h3 class="mb-3 text-sm font-extrabold uppercase tracking-wide text-[var(--muted)]">Assigned to</h3>
          <div class="flex flex-wrap gap-2">
            <div
              v-for="assignee in task.assignees"
              :key="assignee.id"
              class="flex items-center gap-2 rounded-full border border-[var(--line)] bg-[var(--surface)] py-1 pl-1 pr-3"
            >
              <span class="grid h-6 w-6 place-items-center rounded-full bg-[var(--primary)]/10 text-xs font-bold text-[var(--primary)]">
                {{ assignee.name.charAt(0).toUpperCase() }}
              </span>
              <span class="text-sm font-semibold">{{ assignee.name }}</span>
            </div>
          </div>
        </section>

        <section>
          <h3 class="mb-2 text-sm font-extrabold uppercase tracking-wide text-[var(--muted)]">Description</h3>
          <p class="whitespace-pre-wrap leading-relaxed text-[var(--ink)]">
            {{ task.description || 'No description yet.' }}
          </p>
        </section>

        <section>
          <div class="mb-3 flex items-center justify-between">
            <h3 class="text-sm font-extrabold uppercase tracking-wide text-[var(--muted)]">Attachments</h3>
            <span class="text-xs font-bold text-[var(--muted)]">{{ task.attachments?.length ?? 0 }} files</span>
          </div>
          <div v-if="task.attachments?.length" class="space-y-2">
            <div
              v-for="file in task.attachments"
              :key="file.id"
              class="flex items-center gap-3 rounded-lg border border-[var(--line)] p-3 hover:bg-[var(--surface-2)]"
            >
              <TdbIcon name="paperclip" :size="18" class="text-[var(--muted)]" />
              <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold">{{ file.original_name }}</p>
                <span class="text-xs text-[var(--muted)]">{{ file.human_size }}</span>
              </div>
              <a
                :href="route('attachments.download', file.id)"
                class="tdb-btn tdb-btn-ghost !min-h-8 !px-2"
                aria-label="Download attachment"
              >
                <TdbIcon name="download" :size="17" />
              </a>
              <button
                class="tdb-btn tdb-btn-ghost !min-h-8 !px-2 text-[var(--danger)]"
                aria-label="Delete attachment"
                @click="removeAttachment(file.id)"
              >
                <TdbIcon name="trash" :size="17" />
              </button>
            </div>
          </div>
          <p
            v-else
            class="rounded-lg border border-dashed border-[var(--line)] p-4 text-center text-sm font-medium text-[var(--muted)]"
          >
            No attachments.
          </p>
        </section>

        <section>
          <h3 class="mb-3 text-sm font-extrabold uppercase tracking-wide text-[var(--muted)]">Activity</h3>
          <div class="border-l-2 border-[var(--line)] pl-4 text-sm text-[var(--muted)] space-y-2">
            <p v-if="task.creator">
              Created by <strong class="text-[var(--ink)]">{{ task.creator.name }}</strong> on {{ formatDate(task.created_at) }}
            </p>
            <p v-else>
              Created {{ formatDate(task.created_at) }}
            </p>
            <p>Last updated {{ formatDate(task.updated_at) }}</p>
          </div>
        </section>

        <div class="flex flex-wrap gap-2 border-t border-[var(--line)] pt-5">
          <TdbButton variant="secondary" @click="editing = true">
            <TdbIcon name="edit" :size="17" /> Edit
          </TdbButton>
          <TdbButton variant="secondary" @click="markDone">
            <TdbIcon name="check" :size="17" />
            {{ task.status === 'done' ? 'Move to To-do' : 'Mark as done' }}
          </TdbButton>
          <TdbButton class="ml-auto" variant="ghost" @click="confirmingDelete = true">
            <span class="text-[var(--danger)]">Delete</span>
          </TdbButton>
        </div>
      </template>
    </div>
  </TdbDrawer>

  <TdbModal :show="confirmingDelete" title="Delete this task?" @close="confirmingDelete = false">
    <p class="text-[var(--muted)]">This action cannot be undone. Its attachments will also be removed.</p>
    <div class="mt-6 flex justify-end gap-2">
      <TdbButton variant="ghost" @click="confirmingDelete = false">Cancel</TdbButton>
      <TdbButton variant="danger" @click="remove">Delete task</TdbButton>
    </div>
  </TdbModal>
</template>
