<script setup>
defineProps({ value: { type: Number, default: 0 }, label: { type: String, default: '' } });
</script>
<template>
  <div>
    <div class="mb-2 flex items-center justify-between text-sm">
      <span class="text-[var(--muted)]">{{ label }}</span
      ><strong class="tdb-heading text-lg">{{ Math.round(value) }}%</strong>
    </div>
    <div
      class="h-2.5 overflow-hidden rounded-full border border-[var(--line)] bg-[var(--surface-2)]"
      role="progressbar"
      :aria-valuenow="value"
      aria-valuemin="0"
      aria-valuemax="100"
    >
      <div
        class="h-full rounded-full bg-[var(--primary)] prog-fill-anim transition-[width] duration-300"
        :style="{
          width: `${Math.min(100, Math.max(0, value))}%`,
          backgroundImage:
            'repeating-linear-gradient(174deg,rgba(255,255,255,.05) 0 3px,rgba(255,255,255,.18) 3px 4px)',
        }"
      />
    </div>
  </div>
</template>
