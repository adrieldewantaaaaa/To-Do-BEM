<script setup>
import { onBeforeUnmount, onMounted } from 'vue';
import TdbIcon from './TdbIcon.vue';
const props = defineProps({ show: Boolean, title: String, closeable: { type: Boolean, default: true } });
const emit = defineEmits(['close']);
const onKey = (e) => {
  if (e.key === 'Escape' && props.show && props.closeable) emit('close');
};
onMounted(() => document.addEventListener('keydown', onKey));
onBeforeUnmount(() => document.removeEventListener('keydown', onKey));
</script>
<template>
  <Teleport to="body"
    ><Transition name="fade"
      ><div v-if="show" class="modal-backdrop" role="presentation" @mousedown.self="closeable && $emit('close')">
        <section class="modal-panel" role="dialog" aria-modal="true" :aria-label="title">
          <header class="flex items-center justify-between border-b border-[var(--line)] px-5 py-4">
            <h2 class="tdb-heading text-xl">{{ title }}</h2>
            <button
              v-if="closeable"
              class="tdb-btn tdb-btn-ghost !min-h-9 !px-2"
              aria-label="Close dialog"
              @click="$emit('close')"
            >
              <TdbIcon name="close" :size="20" />
            </button>
          </header>
          <div class="p-5"><slot /></div>
        </section></div></Transition
  ></Teleport>
</template>
