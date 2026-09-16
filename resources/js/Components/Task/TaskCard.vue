<script setup>
import TdbBadge from '@/Components/UI/TdbBadge.vue';
import TdbIcon from '@/Components/UI/TdbIcon.vue';
import SketchCheckbox from '@/Components/Sketch/SketchCheckbox.vue';
import { formatDate, priorityLabel } from '@/lib/format';
const props = defineProps({
  task: { type: Object, required: true },
  compact: Boolean,
  draggable: { type: Boolean, default: false },
});
const emit = defineEmits(['open', 'move', 'toggle']);
const priorityTone = (p) => (p === 'high' ? 'danger' : p === 'medium' ? 'warning' : 'neutral');
</script>
<template>
  <article class="tdb-card group p-4" :class="{ 'opacity-75': task.status === 'done' }">
    <div class="flex items-start gap-3">
      <SketchCheckbox
        :checked="task.status === 'done'"
        :aria-label="task.status === 'done' ? 'Mark task incomplete' : 'Mark task complete'"
        @change="$emit('toggle', task)"
      /><button class="min-w-0 flex-1 text-left" @click="$emit('open', task)">
        <h4 class="font-bold leading-snug" :class="{ 'strike-drawn': task.status === 'done' }">{{ task.title }}</h4>
        <p v-if="task.project" class="mt-1 truncate text-xs text-[var(--muted)]">{{ task.project.name }}</p></button
      ><button v-if="draggable" class="drag-handle" aria-label="Drag task">
        <span class="text-xl leading-none">≡</span>
      </button>
    </div>
    <div class="mt-4 flex flex-wrap items-center gap-2">
      <TdbBadge :tone="priorityTone(task.priority)">{{ priorityLabel(task.priority) }}</TdbBadge>
      <div v-if="task.assignees?.length" class="flex -space-x-1.5 ml-1">
        <span
          v-for="assignee in task.assignees.slice(0, 3)"
          :key="assignee.id"
          class="grid h-5 w-5 place-items-center rounded-full border border-white bg-[var(--surface-2)] text-[9px] font-bold shadow-sm"
          :title="assignee.name"
        >
          {{ assignee.name.charAt(0).toUpperCase() }}
        </span>
        <span
          v-if="task.assignees.length > 3"
          class="grid h-5 w-5 place-items-center rounded-full border border-white bg-[var(--surface-3)] text-[9px] font-bold shadow-sm"
        >
          +{{ task.assignees.length - 3 }}
        </span>
      </div>
      <span class="ml-auto text-xs font-semibold" :class="{ 'danger-circle': task.deadline_state === 'overdue' }">
        {{ formatDate(task.deadline, { short: true }) }}
      </span>
      <span
        v-if="task.attachments_count || task.attachments?.length"
        class="inline-flex items-center gap-1 text-xs text-[var(--muted)]"
      >
        <TdbIcon name="paperclip" :size="15" />{{ task.attachments_count ?? task.attachments.length }}
      </span>
    </div>
    <div v-if="draggable" class="mt-3 border-t border-[var(--line)] pt-2">
      <label class="flex items-center justify-between gap-2 text-xs text-[var(--muted)]"
        ><span>Move to</span
        ><select
          :value="task.status"
          class="rounded-md border border-[var(--line)] bg-[var(--surface)] px-2 py-1 text-[var(--ink)]"
          @change="$emit('move', task, $event.target.value)"
        >
          <option value="todo">To-do</option>
          <option value="in_progress">In progress</option>
          <option value="done">Done</option>
        </select></label
      >
    </div>
  </article>
</template>
