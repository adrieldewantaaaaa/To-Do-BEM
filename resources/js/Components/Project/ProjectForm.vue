<script setup>
import { useForm } from '@inertiajs/vue3';
import TdbButton from '@/Components/UI/TdbButton.vue';
import TdbInput from '@/Components/UI/TdbInput.vue';
const props = defineProps({ project: Object });
const editing = !!props.project;
const form = useForm({
  name: props.project?.name ?? '',
  description: props.project?.description ?? '',
  deadline: props.project?.deadline?.slice?.(0, 10) ?? props.project?.deadline ?? '',
  status: props.project?.status ?? 'active',
});
const submit = () =>
  editing ? form.put(route('projects.update', props.project.id)) : form.post(route('projects.store'));
</script>
<template>
  <form class="space-y-5" @submit.prevent="submit">
    <TdbInput
      id="name"
      v-model="form.name"
      label="Project name"
      required
      maxlength="150"
      placeholder="e.g. Website redesign"
      :error="form.errors.name"
    />
    <div>
      <label for="description" class="tdb-label">Description</label
      ><textarea
        id="description"
        v-model="form.description"
        class="tdb-input min-h-32 resize-y"
        maxlength="2000"
        placeholder="What are you working toward?"
        :aria-invalid="!!form.errors.description"
      />
      <div class="mt-1 flex justify-between text-xs">
        <span class="text-[var(--danger)]">{{ form.errors.description }}</span
        ><span class="text-[var(--muted)]">{{ form.description?.length ?? 0 }}/2000</span>
      </div>
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
      <TdbInput
        id="deadline"
        v-model="form.deadline"
        label="Deadline"
        type="date"
        required
        :error="form.errors.deadline"
      />
      <div>
        <label for="status" class="tdb-label">Status <span class="text-[var(--danger)]">*</span></label
        ><select id="status" v-model="form.status" class="tdb-input" :aria-invalid="!!form.errors.status">
          <option value="active">Active</option>
          <option value="completed">Completed</option>
          <option value="archived">Archived</option>
        </select>
        <p v-if="form.errors.status" class="mt-1 text-sm text-[var(--danger)]">{{ form.errors.status }}</p>
      </div>
    </div>
    <div class="flex flex-wrap justify-end gap-2 border-t border-[var(--line)] pt-5">
      <TdbButton :href="editing ? route('projects.show', project.id) : route('projects.index')" variant="ghost"
        >Cancel</TdbButton
      ><TdbButton type="submit" :disabled="form.processing">{{
        form.processing ? 'Saving…' : editing ? 'Save changes' : 'Create project'
      }}</TdbButton>
    </div>
  </form>
</template>
