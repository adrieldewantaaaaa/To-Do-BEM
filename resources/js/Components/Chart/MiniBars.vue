<script setup>
import { computed, ref, onMounted } from 'vue';
const props = defineProps({
  bars: { type: Array, default: () => [] },
  color: { type: String, default: 'var(--primary)' },
  highlight: { type: Number, default: 0 },
});
const max = computed(() => Math.max(1, ...props.bars.map((b) => b.value || 0)));
const ready = ref(false);
onMounted(() => requestAnimationFrame(() => requestAnimationFrame(() => (ready.value = true))));
</script>
<template>
  <div class="flex items-end justify-between gap-1.5" style="height: 96px">
    <div v-for="(b, i) in bars" :key="i" class="flex h-full flex-1 flex-col items-center justify-end gap-1.5">
      <span class="text-[10px] font-bold tabular-nums" :class="b.value ? 'text-[var(--ink)]' : 'text-transparent'">{{
        b.value
      }}</span>
      <div
        class="w-full rounded-md bar"
        :style="{
          height: (ready ? Math.max(b.value ? 6 : 2, (b.value / max) * 74) : 2) + 'px',
          background: i === highlight ? color : `color-mix(in srgb, ${color} 45%, var(--surface-2))`,
          transitionDelay: i * 60 + 'ms',
        }"
      ></div>
      <span class="text-[10px] font-semibold text-[var(--muted)]">{{ b.label }}</span>
    </div>
  </div>
</template>
<style scoped>
.bar {
  transition: height 0.6s cubic-bezier(0.22, 0.61, 0.36, 1);
}
</style>
