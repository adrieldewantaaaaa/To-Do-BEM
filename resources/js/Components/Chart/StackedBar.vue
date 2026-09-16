<script setup>
import { computed, ref, onMounted } from 'vue';
const props = defineProps({ segments: { type: Array, default: () => [] } });
const total = computed(() => props.segments.reduce((s, x) => s + (x.value || 0), 0) || 1);
const ready = ref(false);
onMounted(() => requestAnimationFrame(() => requestAnimationFrame(() => (ready.value = true))));
const pct = (s) => (ready.value ? ((s.value || 0) / total.value) * 100 : 0);
</script>
<template>
  <div>
    <div class="flex h-3.5 w-full overflow-hidden rounded-full bg-[var(--surface-2)]">
      <div
        v-for="(s, i) in segments"
        :key="s.label"
        class="h-full seg"
        :style="{ width: pct(s) + '%', background: s.color, transitionDelay: i * 110 + 'ms' }"
        :title="`${s.label}: ${s.value}`"
      ></div>
    </div>
    <ul class="mt-4 space-y-2.5">
      <li v-for="s in segments" :key="s.label" class="flex items-center gap-2.5 text-sm">
        <span class="h-2.5 w-2.5 flex-none rounded-full" :style="{ background: s.color }"></span>
        <span class="text-[var(--muted)]">{{ s.label }}</span>
        <b class="ml-auto tabular-nums">{{ s.value }}</b>
      </li>
    </ul>
  </div>
</template>
<style scoped>
.seg {
  transition: width 0.7s cubic-bezier(0.22, 0.61, 0.36, 1);
}
</style>
