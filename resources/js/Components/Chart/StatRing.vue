<script setup>
import { ref, computed, onMounted } from 'vue';
const props = defineProps({
  value: { type: Number, default: 0 },
  size: { type: Number, default: 120 },
  stroke: { type: Number, default: 11 },
  color: { type: String, default: 'var(--primary)' },
  track: { type: String, default: 'var(--surface-2)' },
});
const r = computed(() => (props.size - props.stroke) / 2);
const circ = computed(() => 2 * Math.PI * r.value);
const offset = ref(circ.value);
const shown = ref(0);
onMounted(() => {
  const reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const v = Math.min(100, Math.max(0, props.value));
  requestAnimationFrame(() => {
    offset.value = circ.value * (1 - v / 100);
  });
  if (reduce) {
    shown.value = Math.round(v);
    return;
  }
  const dur = 850,
    start = performance.now();
  const tick = (n) => {
    const p = Math.min(1, (n - start) / dur);
    shown.value = Math.round(v * (1 - Math.pow(1 - p, 3)));
    if (p < 1) requestAnimationFrame(tick);
  };
  requestAnimationFrame(tick);
});
</script>
<template>
  <div class="relative inline-grid place-items-center" :style="{ width: size + 'px', height: size + 'px' }">
    <svg :width="size" :height="size" class="-rotate-90">
      <circle :cx="size / 2" :cy="size / 2" :r="r" :stroke="track" :stroke-width="stroke" fill="none" />
      <circle
        :cx="size / 2"
        :cy="size / 2"
        :r="r"
        :stroke="color"
        :stroke-width="stroke"
        fill="none"
        stroke-linecap="round"
        :stroke-dasharray="circ"
        :stroke-dashoffset="offset"
        style="transition: stroke-dashoffset 0.9s cubic-bezier(0.22, 0.61, 0.36, 1)"
      />
    </svg>
    <div class="absolute text-center leading-none">
      <slot :shown="shown"
        ><span class="tdb-heading text-2xl tabular-nums">{{ shown }}%</span></slot
      >
    </div>
  </div>
</template>
