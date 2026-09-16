<script setup>
import { onBeforeUnmount, onMounted } from 'vue';
import TdbIcon from './TdbIcon.vue';
const props = defineProps({ show: Boolean, title: String });
const emit = defineEmits(['close']);
const onKey = (e) => {
  if (e.key === 'Escape' && props.show) emit('close');
};
onMounted(() => document.addEventListener('keydown', onKey));
onBeforeUnmount(() => document.removeEventListener('keydown', onKey));
</script>
<template>
  <Teleport to="body"
    ><Transition name="fade"><div v-if="show" class="drawer-backdrop" @click="$emit('close')" /></Transition
    ><Transition name="slide"
      ><aside v-if="show" class="drawer-panel" role="dialog" aria-modal="true" :aria-label="title">
        <header
          class="sticky top-0 z-10 flex items-center justify-between border-b border-[var(--line)] bg-[var(--surface)] px-5 py-4"
        >
          <h2 class="tdb-heading truncate pr-4 text-xl">{{ title }}</h2>
          <button class="tdb-btn tdb-btn-ghost !min-h-9 !px-2" aria-label="Close drawer" @click="$emit('close')">
            <TdbIcon name="close" :size="20" />
          </button>
        </header>
        <slot /></aside></Transition
  ></Teleport>
</template>
