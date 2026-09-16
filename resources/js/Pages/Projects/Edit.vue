<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ProjectForm from '@/Components/Project/ProjectForm.vue';
defineProps({ project: Object });
</script>
<template>
  <AppLayout title="Edit project"
    ><div class="mx-auto max-w-3xl">
      <div class="mb-6">
        <p class="text-sm font-bold text-[var(--primary)]">Projects / {{ project.name }}</p>
        <h1 class="tdb-heading mt-1 text-3xl sm:text-4xl">Edit the project.</h1>
        <p class="mt-2 text-[var(--muted)]">Keep the scope, deadline, and status accurate.</p>
      </div>
      <section class="tdb-card p-5 sm:p-7"><ProjectForm :project="project" /></section></div
  ></AppLayout>
</template>
