<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import TdbButton from '@/Components/UI/TdbButton.vue';
import TdbInput from '@/Components/UI/TdbInput.vue';
const props = defineProps({
  projectId: { type: Number, required: true },
  roomMembers: { type: Array, default: () => [] },
});
const emit = defineEmits(['saved', 'cancel']);
const fileError = ref('');
const form = useForm({ title: '', description: '', deadline: '', status: 'todo', priority: 'medium', attachments: [], assignees: [] });
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
const submit = () => {
  if (fileError.value) return;
  form.post(route('projects.tasks.store', props.projectId), {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      form.reset();
      emit('saved');
    },
  });
};
</script>
<template>
  <form class="space-y-5" @submit.prevent="submit">
    <TdbInput
      id="task-title"
      v-model="form.title"
      label="Task title"
      required
      maxlength="200"
      placeholder="What needs to be done?"
      :error="form.errors.title"
    />
    <div>
      <label for="task-description" class="tdb-label">Description</label
      ><textarea
        id="task-description"
        v-model="form.description"
        class="tdb-input min-h-28 resize-y"
        maxlength="5000"
        placeholder="Add the useful context…"
      />
      <p v-if="form.errors.description" class="mt-1 text-sm text-[var(--danger)]">{{ form.errors.description }}</p>
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
      <TdbInput
        id="task-deadline"
        v-model="form.deadline"
        label="Deadline"
        type="date"
        required
        :error="form.errors.deadline"
      />
      <div>
        <label for="task-priority" class="tdb-label">Priority</label
        ><select id="task-priority" v-model="form.priority" class="tdb-input">
          <option value="low">Low</option>
          <option value="medium">Medium</option>
          <option value="high">High</option>
        </select>
      </div>
      <div>
        <label for="task-status" class="tdb-label">Status</label
        ><select id="task-status" v-model="form.status" class="tdb-input">
          <option value="todo">To-do</option>
          <option value="in_progress">In progress</option>
          <option value="done">Done</option>
        </select>
      </div>
      <div>
        <label for="task-files" class="tdb-label">Attachments</label
        ><input
          id="task-files"
          class="tdb-input !py-2 text-sm"
          type="file"
          multiple
          accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.zip"
          @change="chooseFiles"
        />
        <p class="mt-1 text-xs" :class="fileError ? 'text-[var(--danger)]' : 'text-[var(--muted)]'">
          {{ fileError || 'Up to 10 files, 10 MB each.' }}
        </p>
      </div>
    </div>
    <!-- Assignee picker — room projects only -->
    <div v-if="roomMembers.length" class="space-y-2">
      <p class="tdb-label">Assign to</p>
      <div class="flex flex-wrap gap-2">
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
      <p v-if="form.errors.assignees" class="text-sm text-[var(--danger)]">{{ form.errors.assignees }}</p>
    </div>
    <div class="flex justify-end gap-2 border-t border-[var(--line)] pt-5">
      <TdbButton variant="ghost" @click="$emit('cancel')">Cancel</TdbButton
      ><TdbButton type="submit" :disabled="form.processing || !!fileError">{{
        form.processing ? 'Creating…' : 'Create task'
      }}</TdbButton>
    </div>
  </form>
</template>
