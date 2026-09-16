<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ProjectForm from '@/Components/Project/ProjectForm.vue';
</script>
<template>
  <AppLayout title="Create project"
    ><div class="mx-auto max-w-3xl">
      <div class="mb-6">
        <p class="text-sm font-bold text-[var(--primary)]">Projects / New</p>
        <h1 class="tdb-heading mt-1 text-3xl sm:text-4xl">Create a project.</h1>
        <p class="mt-2 text-[var(--muted)]">Give the work a clear home, deadline, and starting state.</p>
      </div>
      <section class="tdb-card p-5 sm:p-7"><ProjectForm /></section></div
  ></AppLayout>
</template>
