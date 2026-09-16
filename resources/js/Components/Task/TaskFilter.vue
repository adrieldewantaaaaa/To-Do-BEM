<script setup>
import { reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import TdbButton from '@/Components/UI/TdbButton.vue';
const props = defineProps({
  filters: { type: Object, default: () => ({}) },
  submitRoute: { type: String, default: 'tasks.index' },
});
const form = reactive({
  search: props.filters.search ?? '',
  status: props.filters.status ?? 'all',
  priority: props.filters.priority ?? 'all',
  deadline: props.filters.deadline ?? 'all',
});
const submit = () => router.get(route(props.submitRoute), form, { preserveState: true, replace: true });
const reset = () => {
  Object.assign(form, { search: '', status: 'all', priority: 'all', deadline: 'all' });
  submit();
};
</script>
<template>
  <form
    class="tdb-panel grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-[minmax(180px,1fr)_repeat(3,160px)_auto]"
    @submit.prevent="submit"
  >
    <input
      v-model="form.search"
      class="tdb-input"
      type="search"
      placeholder="Search tasks…"
      aria-label="Search tasks"
    /><select v-model="form.status" class="tdb-input" aria-label="Filter status">
      <option value="all">All statuses</option>
      <option value="todo">To-do</option>
      <option value="in_progress">In progress</option>
      <option value="done">Done</option></select
    ><select v-model="form.deadline" class="tdb-input" aria-label="Filter deadline">
      <option value="all">Any deadline</option>
      <option value="today">Today</option>
      <option value="week">This week</option>
      <option value="month">This month</option>
      <option value="overdue">Overdue</option></select
    ><select v-model="form.priority" class="tdb-input" aria-label="Filter priority">
      <option value="all">Any priority</option>
      <option value="low">Low</option>
      <option value="medium">Medium</option>
      <option value="high">High</option>
    </select>
    <div class="flex gap-2">
      <TdbButton type="submit" class="flex-1">Apply</TdbButton
      ><TdbButton variant="ghost" aria-label="Clear filters" @click="reset">Clear</TdbButton>
    </div>
  </form>
</template>
