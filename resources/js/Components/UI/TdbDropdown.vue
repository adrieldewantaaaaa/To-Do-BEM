<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
const open = ref(false);
const root = ref(null);
const outside = (e) => {
  if (root.value && !root.value.contains(e.target)) open.value = false;
};
onMounted(() => document.addEventListener('click', outside));
onBeforeUnmount(() => document.removeEventListener('click', outside));
</script>
<template>
  <div ref="root" class="relative inline-flex">
    <button
      class="tdb-btn tdb-btn-ghost !min-h-9 !px-2"
      aria-haspopup="menu"
      :aria-expanded="open"
      @click="open = !open"
    >
      <slot name="trigger">•••</slot></button
    ><Transition name="fade"
      ><div
        v-if="open"
        class="absolute right-0 top-full z-30 mt-1 min-w-44 rounded-[10px_8px_11px_7px] border border-[var(--line)] bg-[var(--surface)] p-1.5 shadow-card"
        role="menu"
        @click="open = false"
      >
        <slot /></div
    ></Transition>
  </div>
</template>
