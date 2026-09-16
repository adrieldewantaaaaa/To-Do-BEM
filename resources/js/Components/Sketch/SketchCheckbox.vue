<script setup>
defineProps({ checked: Boolean, label: String });
defineEmits(['change']);
</script>
<template>
  <label class="inline-flex cursor-pointer items-center gap-2"
    ><input
      class="peer sr-only"
      type="checkbox"
      :checked="checked"
      @change="$emit('change', $event.target.checked)"
    /><span
      class="grid h-5 w-5 place-items-center rounded-[5px_4px_6px_3px] border-[1.5px] border-[var(--line)] bg-[var(--surface)] peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-[var(--primary)] peer-checked:border-[var(--primary)] peer-checked:bg-[var(--primary)]"
      ><svg
        v-if="checked"
        class="sketch-check"
        width="15"
        height="15"
        viewBox="0 0 16 16"
        fill="none"
        stroke="white"
        stroke-width="2.2"
        stroke-linecap="round"
        stroke-linejoin="round"
      >
        <path d="m3 8 3 3 7-7" /></svg></span
    ><span v-if="label">{{ label }}</span></label
  >
</template>
