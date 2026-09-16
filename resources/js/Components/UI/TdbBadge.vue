<script setup>
defineProps({ tone: { type: String, default: 'neutral' } });
</script>
<template>
  <span
    class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-xs font-bold capitalize"
    :class="{
      'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-800 dark:bg-blue-950 dark:text-blue-200':
        tone === 'primary',
      'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950 dark:text-emerald-200':
        tone === 'success',
      'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-800 dark:bg-amber-950 dark:text-amber-100':
        tone === 'warning',
      'border-red-200 bg-red-50 text-red-700 dark:border-red-900 dark:bg-red-950 dark:text-red-200': tone === 'danger',
      'border-[var(--line)] bg-[var(--surface-2)] text-[var(--muted)]': tone === 'neutral',
    }"
    ><slot
  /></span>
</template>
