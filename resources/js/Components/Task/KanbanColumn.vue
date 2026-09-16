<script setup>
import draggable from 'vuedraggable';
import TaskCard from './TaskCard.vue';
defineProps({ title: String, status: String, tasks: Array });
defineEmits(['change', 'open', 'move', 'toggle']);
const accent = (status) => ({ todo: 'var(--muted)', in_progress: 'var(--warning)', done: 'var(--success)' })[status];
</script>
<template>
  <section class="kanban-column">
    <header class="flex items-center gap-2 px-4 py-3">
      <span class="h-2.5 w-2.5 rounded-full" :style="{ background: accent(status) }" />
      <h3 class="text-sm font-extrabold uppercase tracking-wide">{{ title }}</h3>
      <span class="ml-auto rounded-full bg-[var(--surface)] px-2 py-0.5 text-xs font-bold text-[var(--muted)]">{{
        tasks.length
      }}</span>
    </header>
    <draggable
      :list="tasks"
      group="tasks"
      item-key="id"
      handle=".drag-handle"
      ghost-class="task-ghost"
      drag-class="task-drag"
      class="min-h-[350px] space-y-3 px-3 pb-4"
      @change="$emit('change', $event, status)"
      ><template #item="{ element }"
        ><TaskCard
          :task="element"
          draggable
          @open="$emit('open', $event)"
          @move="(task, target) => $emit('move', task, target)"
          @toggle="$emit('toggle', $event)" /></template
    ></draggable>
  </section>
</template>
